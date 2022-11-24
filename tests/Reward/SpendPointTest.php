<?php

declare(strict_types=1);

namespace Domain\Reward;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class SpendPointTest extends TestCase
{
    public function testAvailableFalse(): void
    {
        $spendPoint = new SpendPoint(
            'xxx',
            'xxx',
            new DateTimeImmutable(),
            (new DateTimeImmutable())->modify('+1 year')->setTime(0, 0),
            100,
            PointStatus::open()
        );

        $this->assertFalse($spendPoint->isAvailable());
    }
}
