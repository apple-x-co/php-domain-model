<?php

declare(strict_types=1);

namespace Domain\Reward;

use DateTimeImmutable;

class EarningPoint implements PointInterface
{
    /** @var string */
    private $transactionId;

    /** @var string */
    private $pointId;

    /** @var DateTimeImmutable */
    private $pointAt;

    /** @var DateTimeImmutable|null */
    private $expiresAt;

    /** @var int */
    private $amount;

    /** @var PointStatus */
    private $status;

    /** @var int */
    private $remainingAmount;

    /** @var DateTimeImmutable|null */
    private $invalidationAt;

    /** @var string|null */
    private $invalidationReason;

    /**
     * @param int                  $amount
     *
     * @phpstan-param positive-int $amount
     */
    public function __construct(
        string $transactionId,
        string $pointId,
        DateTimeImmutable $pointAt,
        ?DateTimeImmutable $expiresAt,
        int $amount,
        PointStatus $status
    ) {
        $this->transactionId = $transactionId;
        $this->pointId = $pointId;
        $this->pointAt = $pointAt;
        $this->expiresAt = $expiresAt;
        $this->amount = $amount;
        $this->status = $status;
        $this->remainingAmount = $amount;
    }

    /**
     * @param int                  $amount
     *
     * @phpstan-param positive-int $amount
     */
    public static function reconstruct(
        string $transactionId,
        string $pointId,
        DateTimeImmutable $pointAt,
        ?DateTimeImmutable $expiresAt,
        int $amount,
        int $remainingAmount,
        PointStatus $status,
        ?DateTimeImmutable $invalidationAt,
        ?string $invalidationReason
    ): self
    {
        $earningPoint = new self($transactionId, $pointId, $pointAt, $expiresAt, $amount, $status);
        $earningPoint->remainingAmount = $remainingAmount;
        $earningPoint->invalidationAt = $invalidationAt;
        $earningPoint->invalidationReason = $invalidationReason;

        return $earningPoint;
    }

    public function getTransactionId(): string
    {
        return $this->transactionId;
    }

    public function getPointId(): string
    {
        return $this->pointId;
    }

    public function getPointAt(): DateTimeImmutable
    {
        return $this->pointAt;
    }

    public function getExpiresAt(): ?DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getRemainingAmount(): int
    {
        return $this->remainingAmount;
    }

    public function getStatus(): PointStatus
    {
        return $this->status;
    }

    public function getInvalidationAt(): ?DateTimeImmutable
    {
        return $this->invalidationAt;
    }

    public function getInvalidationReason(): ?string
    {
        return $this->invalidationReason;
    }

    public function isAvailable(): bool
    {
        if ($this->invalidationAt !== null) {
            return false;
        }

        $now = new DateTimeImmutable();
        if ($this->expiresAt !== null && $now > $this->expiresAt) {
            return false;
        }

        if (! $this->status->isSame(PointStatus::open())) {
            return false;
        }

        return $this->remainingAmount > 0;
    }

    public function useAmount(int $amount): self
    {
        $clone = clone $this;
        if ($clone->remainingAmount >= $amount) {
            $clone->remainingAmount -= $amount;
        }

        return $clone;
    }

    public function refund(string $reason): self
    {
        $clone = clone $this;
        $clone->invalidationAt = new DateTimeImmutable();
        $clone->invalidationReason = $reason;

        return $clone;
    }
}
