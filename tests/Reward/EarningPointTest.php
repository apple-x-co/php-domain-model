<?php

declare(strict_types=1);

namespace Domain\Reward;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class EarningPointTest extends TestCase
{
    public function testRemainingFull(): void
    {
        $earningPoint = EarningPoint::reconstruct(
            UuidProvider::get(),
            new DateTimeImmutable(),
            (new DateTimeImmutable())->modify('+1 year')->setTime(0, 0),
            100,
            100,
            PointStatus::open(),
            null,
            null
        );

        $this->assertTrue($earningPoint->isAvailable());
    }

    public function testRemainingZero(): void
    {
        $earningPoint = EarningPoint::reconstruct(
            UuidProvider::get(),
            new DateTimeImmutable(),
            (new DateTimeImmutable())->modify('+1 year')->setTime(0, 0),
            0,
            0,
            PointStatus::open(),
            null,
            null
        );

        $this->assertFalse($earningPoint->isAvailable());
    }

    public function testExpired(): void
    {
        $earningPoint = EarningPoint::reconstruct(
            UuidProvider::get(),
            new DateTimeImmutable(),
            (new DateTimeImmutable())->modify('-1 sec'),
            100,
            0,
            PointStatus::open(),
            null,
            null
        );

        $this->assertFalse($earningPoint->isAvailable());
    }

    public function testInvalid(): void
    {
        $earningPoint = EarningPoint::reconstruct(
            UuidProvider::get(),
            new DateTimeImmutable(),
            (new DateTimeImmutable())->modify('+1 sec'),
            100,
            100,
            PointStatus::open(),
            new DateTimeImmutable(),
            null
        );

        $this->assertFalse($earningPoint->isAvailable());
    }
}
