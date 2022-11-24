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

        $this->assertEquals(0, $reward->calculateTotalAmount());
    }

    public function testTotalAmount2(): void
    {
        $now = new DateTimeImmutable();

        $reward = new Reward([
            new EarnedPoint(
                UuidProvider::get(),
                UuidProvider::get(),
                $now,
                $now->modify('1 minute'),
                100,
                PointStatus::open()
            ),
        ]);

        $this->assertEquals(100, $reward->calculateTotalAmount());
    }

    public function testTotalAmount3(): void
    {
        $now = new DateTimeImmutable();

        $reward = new Reward([
            new EarnedPoint(
                UuidProvider::get(),
                UuidProvider::get(),
                $now,
                $now->modify('+1 minute'),
                100,
                PointStatus::open()
            ),
            new EarnedPoint(
                UuidProvider::get(),
                UuidProvider::get(),
                $now,
                $now->modify('+1 minute'),
                50,
                PointStatus::open()
            )
        ]);

        $this->assertEquals(150, $reward->calculateTotalAmount());
    }

    public function testTotalAmount4(): void
    {
        $now = new DateTimeImmutable();

        $reward = new Reward([
            new EarnedPoint(
                UuidProvider::get(),
                UuidProvider::get(),
                $now,
                $now->modify('+1 minute'),
                200,
                PointStatus::open()
            ),
            new EarnedPoint(
                UuidProvider::get(),
                UuidProvider::get(),
                $now,
                $now->modify('-1 sec'),
                50,
                PointStatus::open()
            ),
            new EarnedPoint(
                UuidProvider::get(),
                'xxx-3',
                $now,
                $now->modify('-1 sec'),
                50,
                PointStatus::draft()
            ),
        ]);

        $this->assertEquals(200, $reward->calculateTotalAmount());
    }

    public function testTotalAmount5(): void
    {
        $now = new DateTimeImmutable();

        $reward = new Reward([
            new EarnedPoint(
                UuidProvider::get(),
                UuidProvider::get(),
                $now,
                $now->modify('+1 minute'),
                200,
                PointStatus::open()
            ),
            new EarnedPoint(
                UuidProvider::get(),
                UuidProvider::get(),
                $now,
                $now->modify('-1 sec'),
                50,
                PointStatus::open()
            ),
            new EarnedPoint(
                UuidProvider::get(),
                UuidProvider::get(),
                $now,
                $now->modify('-1 sec'),
                50,
                PointStatus::draft()
            ),
        ]);

        $this->assertEquals(300, $reward->calculateTotalAmount(false));
    }

    public function testEarnPoint1(): void
    {
        $now = new DateTimeImmutable();

        $reward = new Reward([]);
        $reward->earn(111, $now->modify('+1 minute'), PointStatus::open());

        $this->assertEquals(111, $reward->calculateTotalAmount());
    }

    public function testEarnPoint2(): void
    {
        $now = new DateTimeImmutable();

        $reward = new Reward([]);
        $reward->earn(111, $now->modify('+1 minute'), PointStatus::open());
        $reward->earn(111, $now->modify('+1 minute'), PointStatus::open());

        $this->assertEquals(222, $reward->calculateTotalAmount());
    }

    public function testEarnPoint3(): void
    {
        $now = new DateTimeImmutable();

        $reward = new Reward([]);
        $reward->earn(333, $now->modify('+1 minute'), PointStatus::open());
        $reward->earn(111, $now->modify('-1 minute'), PointStatus::open());

        $this->assertEquals(333, $reward->calculateTotalAmount());
    }

    public function testSpendPoint1(): void
    {
        $now = new DateTimeImmutable();

        $reward = new Reward([]);
        $reward->earn(1000, $now->modify('+1 minute'), PointStatus::open());
        $reward->spend(600);

        $this->assertEquals(400, $reward->calculateTotalAmount());
    }

    public function testSpendPoint2(): void
    {
        $now = new DateTimeImmutable();

        $reward = new Reward([]);
        $reward->earn(500, $now->modify('+1 minute'), PointStatus::open());
        $reward->earn(300, $now->modify('+1 minute'), PointStatus::open());
        $reward->spend(700);

        $this->assertEquals(100, $reward->calculateTotalAmount());
    }

    public function testSpendPoint3(): void
    {
        $now = new DateTimeImmutable();

        $reward = new Reward([]);
        $reward->earn(500, $now->modify('+1 minute'), PointStatus::open());
        $reward->earn(300, $now->modify('+1 minute'), PointStatus::open());
        $reward->earn(200, $now->modify('+1 minute'), PointStatus::draft());
        $reward->spend(700);

        $this->assertEquals(100, $reward->calculateTotalAmount());
    }
}
