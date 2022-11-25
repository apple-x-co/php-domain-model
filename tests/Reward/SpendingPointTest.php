<?php

declare(strict_types=1);

namespace Domain\Reward;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class SpendingPointTest extends TestCase
{
    public function testAvailableFalse(): void
    {
        $spendingPoint = new SpendingPoint(
            UuidProvider::get(),
            UuidProvider::get(),
            new DateTimeImmutable(),
            (new DateTimeImmutable())->modify('+1 year')->setTime(0, 0),
            100,
            PointStatus::open()
        );

        $this->assertFalse($spendingPoint->isAvailable());
    }
}
