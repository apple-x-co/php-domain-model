<?php

declare(strict_types=1);

namespace Domain\Reward;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class RewardTest extends TestCase
{
    public function testTotalAmount1(): void
    {
        $reward = new Reward([]);

        $this->assertEquals(0, $reward->calculateRemainingAmount());
    }

    public function testTotalAmount2(): void
    {
        $now = new DateTimeImmutable();

        $reward = new Reward([
            new PointCluster(
                UuidProvider::get(),
                100,
                PointClusterType::earning(),
                [
                    new EarningPoint(
                        UuidProvider::get(),
                        $now,
                        $now->modify('1 minute'),
                        100,
                        PointStatus::open()
                    ),
                ]),
        ]);

        $this->assertEquals(100, $reward->calculateRemainingAmount());
    }

    public function testTotalAmount3(): void
    {
        $now = new DateTimeImmutable();

        $reward = new Reward([
            new PointCluster(
                UuidProvider::get(),
                150,
                PointClusterType::earning(),
                [
                    new EarningPoint(
                        UuidProvider::get(),
                        $now,
                        $now->modify('+1 minute'),
                        100,
                        PointStatus::open()
                    ),
                    new EarningPoint(
                        UuidProvider::get(),
                        $now,
                        $now->modify('+1 minute'),
                        50,
                        PointStatus::open()
                    )
                ]
            ),
        ]);

        $this->assertEquals(150, $reward->calculateRemainingAmount());
    }

    public function testTotalAmount4(): void
    {
        $now = new DateTimeImmutable();

        $reward = new Reward([
            new PointCluster(
                UuidProvider::get(),
                200,
                PointClusterType::earning(),
                [
                    new EarningPoint(
                        UuidProvider::get(),
                        $now,
                        $now->modify('+1 minute'),
                        200,
                        PointStatus::open()
                    ),
                ]
            ),
            new PointCluster(
                UuidProvider::get(),
                100,
                PointClusterType::earning(),
                [
                    new EarningPoint(
                        UuidProvider::get(),
                        $now,
                        $now->modify('-1 sec'),
                        50,
                        PointStatus::open()
                    ),
                    new EarningPoint(
                        UuidProvider::get(),
                        $now,
                        $now->modify('-1 sec'),
                        50,
                        PointStatus::draft()
                    ),
                ]
            ),
        ]);

        $this->assertEquals(200, $reward->calculateRemainingAmount());
    }

    public function testTotalAmount5(): void
    {
        $now = new DateTimeImmutable();

        $reward = new Reward([
            new PointCluster(
                UuidProvider::get(),
                200,
                PointClusterType::earning(),
                [
                    new EarningPoint(
                        UuidProvider::get(),
                        $now,
                        $now->modify('+1 minute'),
                        200,
                        PointStatus::open()
                    ),
                ]
            ),
            new PointCluster(
                UuidProvider::get(),
                100,
                PointClusterType::earning(),
                [
                    new EarningPoint(
                        UuidProvider::get(),
                        $now,
                        $now->modify('-1 sec'),
                        50,
                        PointStatus::open()
                    ),
                    new EarningPoint(
                        UuidProvider::get(),
                        $now,
                        $now->modify('-1 sec'),
                        50,
                        PointStatus::draft()
                    ),
                ]
            ),
        ]);

        $this->assertEquals(300, $reward->calculateTotalAmount());
    }

    public function testEarnPoint1(): void
    {
        $now = new DateTimeImmutable();

        $reward = (new Reward([]))
            ->earn(111, $now->modify('+1 minute'), PointStatus::open());

        $this->assertEquals(111, $reward->calculateRemainingAmount());
    }

    public function testEarnPoint2(): void
    {
        $now = new DateTimeImmutable();

        $reward = (new Reward([]))
            ->earn(111, $now->modify('+1 minute'), PointStatus::open())
            ->earn(111, $now->modify('+1 minute'), PointStatus::open());

        $this->assertEquals(222, $reward->calculateRemainingAmount());
    }

    public function testEarnPoint3(): void
    {
        $now = new DateTimeImmutable();

        $reward = (new Reward([]))
            ->earn(333, $now->modify('+1 minute'), PointStatus::open())
            ->earn(111, $now->modify('-1 minute'), PointStatus::open());

        $this->assertEquals(333, $reward->calculateRemainingAmount());
    }

    public function testSpendPoint1(): void
    {
        $now = new DateTimeImmutable();

        $reward = (new Reward([]))
            ->earn(1000, $now->modify('+1 minute'), PointStatus::open())
            ->spend(600);

        $this->assertEquals(400, $reward->calculateRemainingAmount());
    }

    public function testSpendPoint2(): void
    {
        $now = new DateTimeImmutable();

        $reward = (new Reward([]))
            ->earn(500, $now->modify('+1 minute'), PointStatus::open())
            ->earn(300, $now->modify('+1 minute'), PointStatus::open())
            ->spend(700);

        $this->assertEquals(100, $reward->calculateRemainingAmount());
    }

    public function testSpendPoint3(): void
    {
        $now = new DateTimeImmutable();

        $reward = (new Reward([]))
            ->earn(500, $now->modify('+1 minute'), PointStatus::open())
            ->earn(300, $now->modify('+1 minute'), PointStatus::open())
            ->earn(200, $now->modify('+1 minute'), PointStatus::draft())
            ->spend(800);

        $this->assertEquals(0, $reward->calculateRemainingAmount());
    }
}
