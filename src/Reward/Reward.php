<?php

declare(strict_types=1);

namespace Domain\Reward;

use DateTimeImmutable;
use Domain\Exception\RewardOverSpendException;

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

    public function earn(int $amount, DateTimeImmutable $expiresAt, PointStatus $status): void
    {
        $earnedPoint = new EarnedPoint(
            UuidProvider::get(),
            UuidProvider::get(),
            new DateTimeImmutable(),
            $expiresAt,
            $amount,
            $status
        );

        $this->points[] = $earnedPoint;
    }

    public function spend(int $amount): void
    {
        $totalAmount = $this->calculateTotalAmount();
        if ($totalAmount < $amount) {
            throw new RewardOverSpendException();
        }

        $transactionId = UuidProvider::get();

        $spendAmount = $amount;
        $spendPoints = [];
        foreach ($this->points as $point) {
            if ($point instanceof EarnedPoint) {
                $amount = 0;
                if ($point->getRemainingAmount() >= $spendAmount) {
                    $amount = $spendAmount;
                } else {
                    $amount = $point->getRemainingAmount();
                }

                $spendAmount -= $amount;

                $spendPoints[] = new SpendPoint(
                    $transactionId,
                    $point->getPointId(),
                    new DateTimeImmutable(),
                    $point->getExpiresAt(),
                    $amount,
                    PointStatus::open()
                );

                $point->useAmount($amount);
            }

            if ($spendAmount === 0) {
                break;
            }
        }

        if (empty($spendPoints)) {
            return;
        }

        $this->points = array_merge($this->points, $spendPoints);
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
