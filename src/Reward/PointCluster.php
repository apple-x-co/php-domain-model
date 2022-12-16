<?php

declare(strict_types=1);

namespace Domain\Reward;

use function array_reduce;

class PointCluster
{
    /** @var string */
    private $transactionId;

    /** @var int */
    private $amount;

    /** @var array<PointInterface> */
    private $points;

    public function __construct(string $transactionId, int $amount, array $points)
    {
        $this->transactionId = $transactionId;
        $this->amount = $amount;
        $this->points = $points;
    }

    public function __clone()
    {
        $this->points = array_reduce($this->points, static function (array $carry, PointInterface $item) {
            $carry[] = clone $item;

            return $carry;
        }, []);
    }

    public static function reconstruct(string $transactionId, int $amount, array $points): self
    {
        return new self($transactionId, $amount, $points);
    }

    public function getTransactionId(): string
    {
        return $this->transactionId;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getPoints(): array
    {
        return $this->points;
    }

    public function calculateRemainingAmount(): int
    {
        $remainingAmount = 0;
        foreach ($this->getPoints() as $point) {
            if (! $point->isAvailable()) {
                continue;
            }

            $remainingAmount += $point->getRemainingAmount();
        }

        return $remainingAmount;
    }

    public function calculateTotalAmount(): int
    {
        $totalAmount = 0;
        foreach ($this->getPoints() as $point) {
            $totalAmount += $point->getAmount();
        }

        return $totalAmount;
    }

    /**
     * @param array<PointInterface> $points
     */
    public function changePoints(array $points): self
    {
        $amount = 0;
        foreach ($points as $point) {
            $amount += $point->getAmount();
        }

        $clone = clone $this;
        $clone->amount = $amount;
        $clone->points = $points;

        return $clone;
    }

    public function refresh(): self
    {
        $amount = 0;
        $newPoints = [];

        foreach ($this->points as $point) {
            $newPoint = clone $point;

            if (! $newPoint->isInvalid() && $newPoint->isExpired()) {
                $newPoint = $newPoint->expires();
            }

            $amount += $newPoint->getAmount();

            $newPoints[] = $newPoint;
        }

        $clone = clone $this;
        $clone->amount = $amount;
        $clone->points = $newPoints;

        return $clone;
    }
}
