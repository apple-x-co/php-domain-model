<?php

declare(strict_types=1);

namespace Domain\Reward;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class RewardTest extends TestCase
{
    public function testTotalAmount1(): void
    {
        $reward = new Reward();

        $this->assertEquals(0, $reward->calculateRemainingAmount());
    }

    public function testTotalAmount2(): void
    {
        $now = new DateTimeImmutable();

        $reward = new Reward([
            new PointCluster(
                UuidProvider::get(),
                100,
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

        $reward = (new Reward())
            ->earn(UuidProvider::get(), 111, $now->modify('+1 minute'), PointStatus::open());

        $this->assertEquals(111, $reward->calculateRemainingAmount());
    }

    public function testEarnPoint2(): void
    {
        $now = new DateTimeImmutable();

        $reward = (new Reward())
            ->earn(UuidProvider::get(), 111, $now->modify('+1 minute'), PointStatus::open())
            ->earn(UuidProvider::get(), 111, $now->modify('+1 minute'), PointStatus::open());

        $this->assertEquals(222, $reward->calculateRemainingAmount());
    }

    public function testEarnPoint3(): void
    {
        $now = new DateTimeImmutable();

        $reward = (new Reward())
            ->earn(UuidProvider::get(), 333, $now->modify('+1 minute'), PointStatus::open())
            ->earn(UuidProvider::get(), 111, $now->modify('-1 minute'), PointStatus::open());

        $this->assertEquals(333, $reward->calculateRemainingAmount());
    }

    public function testSpendPoint1(): void
    {
        $now = new DateTimeImmutable();

        $reward = (new Reward())
            ->earn(UuidProvider::get(), 1000, $now->modify('+1 minute'), PointStatus::open())
            ->spend(UuidProvider::get(), 600);

        $this->assertEquals(400, $reward->calculateRemainingAmount());
    }

    public function testSpendPoint2(): void
    {
        $now = new DateTimeImmutable();

        $reward = (new Reward())
            ->earn(UuidProvider::get(), 500, $now->modify('+1 minute'), PointStatus::open())
            ->earn(UuidProvider::get(), 300, $now->modify('+1 minute'), PointStatus::open())
            ->spend(UuidProvider::get(), 700);

        $this->assertEquals(100, $reward->calculateRemainingAmount());
    }

    public function testSpendPoint3(): void
    {
        $now = new DateTimeImmutable();

        $reward = (new Reward())
            ->earn(UuidProvider::get(), 500, $now->modify('+1 minute'), PointStatus::open())
            ->earn(UuidProvider::get(), 300, $now->modify('+1 minute'), PointStatus::open())
            ->earn(UuidProvider::get(), 200, $now->modify('+1 minute'), PointStatus::draft())
            ->spend(UuidProvider::get(), 800);

        $this->assertEquals(0, $reward->calculateRemainingAmount());
    }

    public function testRefundAllEarning(): void
    {
        $now = new DateTimeImmutable();
        $transactionId = UuidProvider::get();

        $reward = (new Reward())
            ->earn($transactionId, 500, $now->modify('+1 minute'), PointStatus::open())
            ->refund($transactionId, 'TEST');

        $this->assertEquals(500, $reward->calculateTotalAmount());
        $this->assertEquals(0, $reward->calculateRemainingAmount());
    }

    public function testPointCluster(): void
    {
        $now = new DateTimeImmutable();
        $transactionId = UuidProvider::get();

        $reward = (new Reward())
            ->earn($transactionId, 500, $now->modify('+1 minute'), PointStatus::open());
        $pointClusters = $reward->getPointClusters();
        $pointCluster = $pointClusters[0];

        $this->assertEquals(500, $pointCluster->getAmount());
    }

    public function testRefundEarning(): void
    {
        $now = new DateTimeImmutable();
        $transactionId1 = UuidProvider::get();
        $transactionId2 = UuidProvider::get();
        $transactionId3 = UuidProvider::get();

        $reward = (new Reward())
            ->earn($transactionId1, 500, $now->modify('+1 minute'), PointStatus::open())
            ->earn($transactionId2, 300, $now->modify('+1 minute'), PointStatus::open())
            ->earn($transactionId3, 200, $now->modify('+1 minute'), PointStatus::draft())
            ->refund($transactionId1, 'TEST');

        $this->assertEquals(1000, $reward->calculateTotalAmount());
        $this->assertEquals(300, $reward->calculateRemainingAmount());
    }

    public function testRefundAllSpending(): void
    {
        $now = new DateTimeImmutable();
        $transactionId1 = UuidProvider::get();
        $transactionId2 = UuidProvider::get();

        $reward = (new Reward())
            ->earn($transactionId1, 500, $now->modify('+1 minute'), PointStatus::open())
            ->spend($transactionId2, 500)
            ->refund($transactionId2, 'TEST');

        $this->assertEquals(500, $reward->calculateTotalAmount());
        $this->assertEquals(500, $reward->calculateRemainingAmount());
    }

    public function testRefresh(): void
    {
        $now = new DateTimeImmutable();
        $transactionId = UuidProvider::get();

        $reward = (new Reward())
            ->earn($transactionId, 500, $now->modify('+1 second'), PointStatus::open());

        sleep(1);

        $reward = $reward->refresh();

        $this->assertEquals(0, $reward->calculateRemainingAmount());
    }
}
