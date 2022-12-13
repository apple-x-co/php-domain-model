<?php

declare(strict_types=1);

namespace Domain\Reward;

use DateTimeImmutable;

class SpendingPoint implements PointInterface
{
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

    /** @var DateTimeImmutable|null */
    private $invalidationAt;

    /** @var string|null */
    private $invalidationReason;

    /**
     * @param int                  $amount
     *
     * @phpstan-param negative-int $amount
     */
    public function __construct(
        string $pointId,
        DateTimeImmutable $pointAt,
        ?DateTimeImmutable $expiresAt,
        int $amount,
        PointStatus $status
    ) {
        $this->pointId = $pointId;
        $this->pointAt = $pointAt;
        $this->expiresAt = $expiresAt;
        $this->amount = $amount;
        $this->status = $status;
    }

    public function __clone()
    {
        $this->pointAt = clone $this->pointAt;
        $this->expiresAt = $this->expiresAt === null ? null : clone $this->expiresAt;
        $this->status = clone $this->status;
        $this->invalidationAt = $this->invalidationAt === null ? null : clone $this->invalidationAt;
    }

    /**
     * @param int                  $amount
     *
     * @phpstan-param negative-int $amount
     */
    public static function reconstruct(
        string $pointId,
        DateTimeImmutable $pointAt,
        ?DateTimeImmutable $expiresAt,
        int $amount,
        PointStatus $status,
        ?DateTimeImmutable $invalidationAt,
        ?string $invalidationReason
    ): self
    {
        $spendingPoint = new self($pointId, $pointAt, $expiresAt, $amount, $status);
        $spendingPoint->invalidationAt = $invalidationAt;
        $spendingPoint->invalidationReason = $invalidationReason;

        return $spendingPoint;
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

    public function getStatus(): PointStatus
    {
        return $this->status;
    }

    public function getRemainingAmount(): int
    {
        return 0;
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
        return false;
    }

    public function refund(string $reason): PointInterface
    {
        $clone = clone $this;
        $clone->invalidationAt = new DateTimeImmutable();
        $clone->invalidationReason = $reason;

        return $clone;
    }
}
