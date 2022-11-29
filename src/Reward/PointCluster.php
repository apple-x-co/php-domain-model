<?php

declare(strict_types=1);

namespace Domain\Reward;

use Domain\Exception\InvalidAmountTypeException;
use Domain\Exception\InvalidPointTypeException;
use function array_reduce;

class PointCluster
{
    /** @var string */
    private $transactionId;

    /** @var int */
    private $amount;

    /** @var PointClusterType */
    private $type;

    /** @var array<PointInterface> */
    private $points;

    public function __construct(string $transactionId, int $amount, PointClusterType $type, array $points)
    {
        $this->transactionId = $transactionId;
        $this->amount = $amount;
        $this->type = $type;
        $this->points = $points;
    }

    public function __clone()
    {
        $this->type = clone $this->type;
        $this->points = array_reduce($this->points, static function (array $carry, PointInterface $item) {
            $carry[] = clone $item;

            return $carry;
        }, []);
    }

    public static function reconstruct(string $transactionId, int $amount, PointClusterType $type, array $points): self
    {
        $earningPointCluster = PointClusterType::earning();
        $spendingPointCluster = PointClusterType::spending();

        foreach ($points as $point) {
            if (($point instanceof EarningPoint) === false && $type->isSame($earningPointCluster)) {
                throw new InvalidPointTypeException();
            }

            if (($point instanceof SpendingPoint) === false && $type->isSame($spendingPointCluster)) {
                throw new InvalidPointTypeException();
            }
        }

        if ($amount < 0 && $type->isSame($earningPointCluster)) {
            throw new InvalidAmountTypeException();
        }

        if ($amount > 0 && $type->isSame($spendingPointCluster)) {
            throw new InvalidAmountTypeException();
        }

        return new self($transactionId, $amount, $type, $points);
    }

    public function getTransactionId(): string
    {
        return $this->transactionId;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getType(): PointClusterType
    {
        return $this->type;
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
        $clone = clone $this;
        $clone->points = $points;

        return $clone;
    }
}
