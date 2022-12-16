<?php

declare(strict_types=1);

namespace Domain\Reward;

use Domain\ValueObject\StringTrait;
use Domain\ValueObjectInterface;

class PointStatus implements ValueObjectInterface
{
    use StringTrait;

    private const DRAFT = 'draft';
    private const OPEN = 'open';
    private const ARCHIVED = 'archived';
    private const CANCELED = 'canceled';

    public static function draft(): self
    {
        return self::fromPrimitive(self::DRAFT);
    }

    public static function open(): self
    {
        return self::fromPrimitive(self::OPEN);
    }

//    public static function archived(): self
//    {
//        return self::fromPrimitive(self::ARCHIVED);
//    }
//
//    public static function canceled(): self
//    {
//        return self::fromPrimitive(self::CANCELED);
//    }
}
