<?php

declare(strict_types=1);

namespace Domain\Reward;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class PointClusterTest extends TestCase
{
    public function testRemainingAmountZero(): void
    {
        $pointCluster = PointCluster::reconstruct(
            UuidProvider::get(),
            0,
            PointClusterType::earning(),
            []
        );

        $this->assertEquals(0, $pointCluster->calculateRemainingAmount());
    }

    public function testRemainingAmountFull(): void
    {
        $pointCluster = PointCluster::reconstruct(
            UuidProvider::get(),
            100,
            PointClusterType::earning(),
            [
                EarningPoint::reconstruct(
                    UuidProvider::get(),
                    new DateTimeImmutable(),
                    (new DateTimeImmutable())->modify('+1 year')->setTime(0, 0),
                    100,
                    100,
                    PointStatus::open(),
                    null,
                    null
                )
            ]
        );

        $this->assertEquals(100, $pointCluster->calculateRemainingAmount());
    }

    public function testChangePoint(): void
    {
        $pointCluster = PointCluster::reconstruct(
            UuidProvider::get(),
            100,
            PointClusterType::earning(),
            [
                EarningPoint::reconstruct(
                    UuidProvider::get(),
                    new DateTimeImmutable(),
                    (new DateTimeImmutable())->modify('+1 year')->setTime(0, 0),
                    100,
                    100,
                    PointStatus::open(),
                    null,
                    null
                )
            ]
        );

        $pointCluster = $pointCluster->changePoints([
            new EarningPoint(
                UuidProvider::get(),
                new DateTimeImmutable(),
                (new DateTimeImmutable())->modify('1 minute'),
                300,
                PointStatus::open()
            ),
        ]);

        $this->assertEquals(300, $pointCluster->getAmount());
    }
}
