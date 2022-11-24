<?php

declare(strict_types=1);

namespace Domain\ValueObject;

use Domain\Exception\BooleanTypeException;
use Domain\ValueObjectInterface;

trait BooleanTrait
{
    /** @var bool */
    protected $boolean;

    public function __construct(bool $boolean)
    {
        $this->boolean = $boolean;
    }

    public function isNull(): bool
    {
        return $this->boolean === null;
    }

    public function isSame(ValueObjectInterface $valueObject): bool
    {
        return $this->toPrimitive() === $valueObject->toPrimitive();
    }

    public static function fromPrimitive($primitive): self
    {
        if (! is_bool($primitive)) {
            throw new BooleanTypeException('Can only instantiate this object with a boolean.');
        }

        return new self($primitive);
    }

    public function toPrimitive(): bool
    {
        return $this->boolean;
    }

    public static function true(): self
    {
        return new self(true);
    }

    public static function false(): self
    {
        return new self(false);
    }

    public function isTrue(): bool
    {
        return $this->toPrimitive() === true;
    }

    public function isFalse(): bool
    {
        return $this->toPrimitive() === false;
    }
}
