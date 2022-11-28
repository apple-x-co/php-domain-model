<?php

declare(strict_types=1);

namespace Domain\Reward;

use DateTimeImmutable;
use Domain\Exception\RewardSpendingOverException;

class Reward
{
    /** @var array<PointInterface> */
    private $points;

    public function __construct(array $points)
    {
        $this->points = $points;
    }

    public static function reconstruct(array $points): self
    {
        return new self($points);
    }

    public function calculateTotalAmount($isAvailable = true): int
    {
        $totalAmount = 0;
        foreach ($this->points as $point) {
            if ($isAvailable && ! $point->isAvailable()) {
                continue;
            }

            $totalAmount += $point->getRemainingAmount();
        }

        return $totalAmount;
    }

    public function earn(int $amount, DateTimeImmutable $expiresAt, PointStatus $status): self
    {
        $clone = clone $this;
        $clone->points[] = new EarningPoint(
            UuidProvider::get(),
            UuidProvider::get(),
            new DateTimeImmutable(),
            $expiresAt,
            $amount,
            $status
        );

        return $clone;
    }

    public function spend(int $amount): self
    {
        $clone = clone $this;

        $totalAmount = $clone->calculateTotalAmount();
        if ($totalAmount < $amount) {
            throw new RewardSpendingOverException();
        }

        $transactionId = UuidProvider::get();

        $spendingAmount = $amount;
        $addPoints = [];
        foreach ($clone->points as &$point) {
            if ($point instanceof EarningPoint) {
                $amount = 0;
                if ($point->getRemainingAmount() >= $spendingAmount) {
                    $amount = $spendingAmount;
                } else {
                    $amount = $point->getRemainingAmount();
                }

                $spendingAmount -= $amount;

                $addPoints[] = new SpendingPoint(
                    $transactionId,
                    $point->getPointId(),
                    new DateTimeImmutable(),
                    $point->getExpiresAt(),
                    $amount,
                    PointStatus::open()
                );

                $point = $point->useAmount($amount);
            }

            if ($spendingAmount === 0) {
                break;
            }
        }
        unset($point);

        $clone->points = array_merge($clone->points, $addPoints);

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
