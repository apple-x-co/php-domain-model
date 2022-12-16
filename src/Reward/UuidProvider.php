<?php

declare(strict_types=1);

namespace Domain\Reward;

use Ramsey\Uuid\Uuid;

class UuidProvider
{
    public static function get(): string
    {
        return Uuid::uuid6()->toString();
    }
}
