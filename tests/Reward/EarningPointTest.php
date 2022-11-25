<?php

declare(strict_types=1);

namespace Domain\Reward;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class EarningPointTest extends TestCase
{
    public function testAvailableTrue(): void
    {
        $earningPoint = new EarningPoint(
            UuidProvider::get(),
            UuidProvider::get(),
            new DateTimeImmutable(),
            (new DateTimeImmutable())->modify('+1 year')->setTime(0, 0),
            100,
            PointStatus::open()
        );

        $this->assertTrue($earningPoint->isAvailable());
    }

    public function testAvailableFalse1(): void
    {
        $earningPoint = new EarningPoint(
            UuidProvider::get(),
            UuidProvider::get(),
            new DateTimeImmutable(),
            (new DateTimeImmutable())->modify('+1 year')->setTime(0, 0),
            0,
            PointStatus::open()
        );

        $this->assertFalse($earningPoint->isAvailable());
    }

    public function testAvailableFalse2(): void
    {
        $earningPoint = new EarningPoint(
            UuidProvider::get(),
            UuidProvider::get(),
            new DateTimeImmutable(),
            (new DateTimeImmutable())->modify('-1 sec'),
            100,
            PointStatus::open()
        );

        $this->assertFalse($earningPoint->isAvailable());
    }
}
