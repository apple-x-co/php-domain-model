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
//
//    public function refund(PointInterface $point): void
//    {
//        // TODO: ポイント戻し
//    }
}
