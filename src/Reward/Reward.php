<?php

declare(strict_types=1);

namespace Domain\Reward;

use DateTimeImmutable;
use Domain\Exception\OverSpendingException;

class Reward
{
    /** @var array<PointCluster> */
    private $pointClusters;

    public function __construct(array $pointClusters)
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

    public function earn(int $amount, DateTimeImmutable $expiresAt, PointStatus $status): self
    {
        $clone = clone $this;
        $clone->pointClusters[] = new PointCluster(
            UuidProvider::get(),
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

    public function spend(int $amount): self
    {
        $remainingAmount = $this->calculateRemainingAmount();
        if ($remainingAmount < $amount) {
            throw new OverSpendingException();
        }

        $earningPointCluster = PointClusterType::earning();

        $spendingAmount = $amount;
        $spendingPoints = [];

        $pointClusters = [];
        foreach ($this->pointClusters as $pointCluster) {
            if (! $pointCluster->getType()->isSame($earningPointCluster)) {
                $pointClusters[] = clone $pointCluster;

                continue;
            }

            $remainingAmount = $pointCluster->calculateRemainingAmount();
            if ($remainingAmount <= 0) {
                $pointClusters[] = clone $pointCluster;

                continue;
            }

            $points = [];
            foreach ($pointCluster->getPoints() as $point) {
                if (! ($point instanceof EarningPoint) || $spendingAmount === 0) {
                    $points[] = clone $point;

                    continue;
                }

                $amount = min($point->getRemainingAmount(), $spendingAmount);
                $spendingAmount -= $amount;

                $spendingPoints[] = new SpendingPoint(
                    $point->getPointId(),
                    new DateTimeImmutable(),
                    $point->getExpiresAt(),
                    $amount * -1,
                    PointStatus::open()
                );

                $points[] = $point->useAmount($amount);
            }

            $pointClusters[] = $pointCluster->changePoints($points);
        }

        if (! empty($spendingPoints)) {
            $pointClusters[] = new PointCluster(
                UuidProvider::get(),
                $amount * -1,
                PointClusterType::spending(),
                $spendingPoints
            );
        }

        $clone = clone $this;
        $clone->pointClusters = $pointClusters;

        return $clone;
    }

//    public function invalidate(PointInterface $point): void
//    {
//        // TODO: ポイント無効化
//    }

//    // TODO: ポイント返却
//    public function refund(PointInterface $point, string $reason): self
//    {
//        if ($point instanceof EarningPoint) {
//            return $this->refundEarningPoint($point, $reason);
//        }
//
//        if ($point instanceof SpendingPoint) {
//            return $this->refundSpendingPoint($point);
//        }
//
//        throw new PointInvalidTypeException();
//    }
//
//    private function refundEarningPoint(EarningPoint $earningPoint, string $reason): self
//    {
//        $clone = clone $this;
//
//        foreach ($clone->points as &$point) {
//            if ($point->getPointId() !== $earningPoint->getPointId()) {
//                continue;
//            }
//
//            if ($point->getInvalidationAt() !== null) {
//                continue;
//            }
//
//            if ($point instanceof EarningPoint) {
//                $point = $point->refund($reason);
//                break;
//            }
//        }
//
//        return $clone;
//    }
//
//    private function refundSpendingPoint(SpendingPoint $spendingPoint): self
//    {
//        // 消費ポイントを戻す場合（例：購入時にポイントを使った）
//    }
}
