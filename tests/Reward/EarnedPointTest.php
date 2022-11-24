<?php

declare(strict_types=1);

namespace Domain\Reward;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class EarnedPointTest extends TestCase
{
    public function testAvailableTrue(): void
    {
        $earnedPoint = new EarnedPoint(
            'xxx',
            'xxx',
            new DateTimeImmutable(),
            (new DateTimeImmutable())->modify('+1 year')->setTime(0, 0),
            100,
            PointStatus::open()
        );

        $this->assertTrue($earnedPoint->isAvailable());
    }

    public function testAvailableFalse1(): void
    {
        $earnedPoint = new EarnedPoint(
            'xxx',
            'xxx',
            new DateTimeImmutable(),
            (new DateTimeImmutable())->modify('+1 year')->setTime(0, 0),
            0,
            PointStatus::open()
        );

        $this->assertFalse($earnedPoint->isAvailable());
    }

    public function testAvailableFalse2(): void
    {
        $earnedPoint = new EarnedPoint(
            'xxx',
            'xxx',
            new DateTimeImmutable(),
            (new DateTimeImmutable())->modify('-1 sec'),
            100,
            PointStatus::open()
        );

        $this->assertFalse($earnedPoint->isAvailable());
    }
}
