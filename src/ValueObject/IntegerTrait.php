<?php

declare(strict_types=1);

namespace Domain\ValueObject;

use Domain\Exception\IntegerTypeException;
use Domain\ValueObjectInterface;

trait IntegerTrait
{
    /** @var int */
    protected $integer;

    public function __construct(int $integer)
    {
        $this->integer = $integer;
    }

    public function isNull(): bool
    {
        return $this->integer === null;
    }

    public function isSame(ValueObjectInterface $valueObject): bool
    {
        return $this->toPrimitive() === $valueObject->toPrimitive();
    }

    public static function fromPrimitive($primitive): self
    {
        if (! is_int($primitive)) {
            throw new IntegerTypeException('Can only instantiate this object with a integer.');
        }

        return new self($primitive);
    }

    public function toPrimitive(): int
    {
        return $this->integer;
    }
}
