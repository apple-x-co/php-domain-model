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

    public static function reconstruct(array $points): self
    {
        return new self($points);
    }

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
            PointClusterType::earning(),
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

        $earningPointCluster = PointClusterType::earning();

        $spendingAmount = $amount;
        $newSpendingPoints = [];
        $newPointClusters = [];

        foreach ($this->pointClusters as $pointCluster) {
            if (! $pointCluster->getType()->isSame($earningPointCluster)) {
                $newPointClusters[] = clone $pointCluster;

                continue;
            }

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
                PointClusterType::spending(),
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

            if ($pointCluster->getType()->isSame(PointClusterType::earning())) {
                $newPointClusters[] = $this->refundEarning($pointCluster, $reason);
            }

            if ($pointCluster->getType()->isSame(PointClusterType::spending())) {
                $newPointClusters[] = $this->refundSpending($pointCluster, $reason);
            }
        }

        $clone = clone $this;
        $clone->pointClusters = $newPointClusters;

        return $clone;
    }

    private function refundEarning(PointCluster $pointCluster, string $reason): PointCluster
    {
        $points = $pointCluster->getPoints();
        foreach ($points as &$point) {
            if ($point instanceof EarningPoint) {
                $point = $point->refund($reason);
            }
        }

        return $pointCluster->changePoints($points);
    }

    private function refundSpending(PointCluster $pointCluster, string $reason): PointCluster
    {
        $newPoints = [];

        $points = $pointCluster->getPoints();
        foreach ($points as &$point) {
            if ($point instanceof SpendingPoint) {
                $point = $point->refund($reason);
                $newPoints[] = new EarningPoint(
                    UuidProvider::get(),
                    $point->getPointAt(),
                    $point->getExpiresAt(),
                    $point->getAmount() * -1,
                    $point->getStatus()
                );
            }
        }

        return $pointCluster->changePoints(array_merge($points, $newPoints));
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
