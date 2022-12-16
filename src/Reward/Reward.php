<?php

declare(strict_types=1);

namespace Domain\Reward;

use DateTimeImmutable;
use Domain\Exception\OverSpendingException;
use function array_merge;

class Reward
{
    /** @var array<PointCluster> */
    private $pointClusters;

    public function __construct(array $pointClusters = [])
    {
        $this->pointClusters = $pointClusters;
    }

//    public static function reconstruct(array $points): self
//    {
//        return new self($points);
//    }

    public function getPointClusters(): array
    {
        return $this->pointClusters;
    }

    public function calculateRemainingAmount(): int
    {
        $remainingAmount = 0;
        foreach ($this->pointClusters as $pointCluster) {
            $remainingAmount += $pointCluster->calculateRemainingAmount();
        }

        return $remainingAmount;
    }

    public function calculateTotalAmount(): int
    {
        $totalAmount = 0;
        foreach ($this->pointClusters as $pointCluster) {
            $totalAmount += $pointCluster->calculateTotalAmount();
        }

        return $totalAmount;
    }

    public function earn(string $transactionId, int $amount, DateTimeImmutable $expiresAt, PointStatus $status): self
    {
        $clone = clone $this;
        $clone->pointClusters[] = new PointCluster(
            $transactionId,
            $amount,
            [
                new EarningPoint(
                    UuidProvider::get(),
                    new DateTimeImmutable(),
                    $expiresAt,
                    $amount,
                    $status
                )
            ]
        );

        return $clone;
    }

    public function spend(string $transactionId, int $amount): self
    {
        $remainingAmount = $this->calculateRemainingAmount();
        if ($remainingAmount < $amount) {
            throw new OverSpendingException();
        }

        $spendingAmount = $amount;
        $newSpendingPoints = [];
        $newPointClusters = [];

        foreach ($this->pointClusters as $pointCluster) {
            $remainingAmount = $pointCluster->calculateRemainingAmount();
            if ($remainingAmount <= 0) {
                $newPointClusters[] = clone $pointCluster;

                continue;
            }

            $newPoints = [];
            foreach ($pointCluster->getPoints() as $point) {
                if (! ($point instanceof EarningPoint) || $spendingAmount === 0) {
                    $newPoints[] = clone $point;

                    continue;
                }

                $amount = min($point->getRemainingAmount(), $spendingAmount);
                $spendingAmount -= $amount;

                $newSpendingPoints[] = new SpendingPoint(
                    $point->getPointId(),
                    new DateTimeImmutable(),
                    $point->getExpiresAt(),
                    $amount * -1,
                    PointStatus::open()
                );

                $newPoints[] = $point->useAmount($amount);
            }

            $newPointClusters[] = $pointCluster->changePoints($newPoints);
        }

        if (! empty($newSpendingPoints)) {
            $newPointClusters[] = new PointCluster(
                $transactionId,
                $amount * -1,
                $newSpendingPoints
            );
        }

        $clone = clone $this;
        $clone->pointClusters = $newPointClusters;

        return $clone;
    }

    public function refund(string $transactionId, string $reason): self
    {
        $newPointClusters = [];

        foreach ($this->pointClusters as $pointCluster) {
            if ($pointCluster->getTransactionId() !== $transactionId) {
                $newPointClusters[] = clone $pointCluster;

                continue;
            }

            $newPoints = [];
            $points = $pointCluster->getPoints();
            foreach ($points as &$point) {
                $point = $point->refund($reason);
                if ($point instanceof SpendingPoint) {
                    $newPoints[] = new EarningPoint(
                        UuidProvider::get(),
                        $point->getPointAt(),
                        $point->getExpiresAt(),
                        $point->getAmount() * -1,
                        $point->getStatus()
                    );
                }
            }
            unset($point);

            $newPointClusters[] = $pointCluster->changePoints(array_merge($points, $newPoints));
        }

        $clone = clone $this;
        $clone->pointClusters = $newPointClusters;

        return $clone;
    }

    public function refresh(): self
    {
        $newPointClusters = [];

        foreach ($this->pointClusters as $pointCluster) {
            $newPointClusters[] = $pointCluster->refresh();
        }

        $clone = clone $this;
        $clone->pointClusters = $newPointClusters;

        return $clone;
    }
}
