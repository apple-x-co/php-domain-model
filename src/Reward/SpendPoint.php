<?php

declare(strict_types=1);

namespace Domain\Reward;

use DateTimeImmutable;

class SpendPoint implements PointInterface
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
    }

    /**
     * @param int                  $amount
     *
     * @phpstan-param negative-int $amount
     */
    public static function reconstruct(
        string $transactionId,
        string $pointId,
        DateTimeImmutable $pointAt,
        ?DateTimeImmutable $expiresAt,
        int $amount,
        PointStatus $status,
        ?DateTimeImmutable $invalidationAt,
        ?string $invalidationReason
    ): self {
        $spendPoint = new self($transactionId, $pointId, $pointAt, $expiresAt, $amount, $status);
        $spendPoint->invalidationAt = $invalidationAt;
        $spendPoint->invalidationReason = $invalidationReason;

        return $spendPoint;
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
}
