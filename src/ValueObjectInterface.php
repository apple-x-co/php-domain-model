<?php

declare(strict_types=1);

namespace Domain;

interface ValueObjectInterface
{
    public function isNull(): bool;

    public function isSame(ValueObjectInterface $valueObject): bool;

    /**
     * @param mixed $primitive
     */
    public static function fromPrimitive($primitive);

    /**
     * @return mixed
     */
    public function toPrimitive();
}
