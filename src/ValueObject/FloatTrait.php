<?php

declare(strict_types=1);

namespace Domain\ValueObject;

use Domain\Exception\FloatTypeException;
use Domain\ValueObjectInterface;

trait FloatTrait
{
    /** @var int */
    protected $float;

    public function __construct(float $float)
    {
        $this->float = $float;
    }

    public function isNull(): bool
    {
        return $this->float === null;
    }

    public function isSame(ValueObjectInterface $valueObject): bool
    {
        return $this->toPrimitive() === $valueObject->toPrimitive();
    }

    public static function fromPrimitive($primitive): self
    {
        if (! is_float($primitive)) {
            throw new FloatTypeException('Can only instantiate this object with a float.');
        }

        return new self($primitive);
    }

    public function toPrimitive(): int
    {
        return $this->float;
    }
}
