<?php

declare(strict_types=1);

namespace Domain\Reward;

use DateTimeImmutable;

interface PointInterface
{
    public function getTransactionId(): string;

    public function getPointId(): string;

    public function getPointAt(): DateTimeImmutable;

    public function getExpiresAt(): ?DateTimeImmutable;

    public function getAmount(): int;

    public function getStatus(): PointStatus;

    public function getRemainingAmount(): int;

    public function getInvalidationAt(): ?DateTimeImmutable;

    public function getInvalidationReason(): ?string;

    public function isAvailable(): bool;
}
