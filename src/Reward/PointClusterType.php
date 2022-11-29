<?php

declare(strict_types=1);

namespace Domain\Reward;

use Domain\ValueObject\StringTrait;
use Domain\ValueObjectInterface;

class PointClusterType implements ValueObjectInterface
{
    use StringTrait;

    private const EARNING = 'earning';
    private const SPENDING = 'spending';

    public static function earning(): self
    {
        return self::fromPrimitive(self::EARNING);
    }

    public static function spending(): self
    {
        return self::fromPrimitive(self::SPENDING);
    }
}
