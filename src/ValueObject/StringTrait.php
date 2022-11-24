<?php

declare(strict_types=1);

namespace Domain\ValueObject;

use Domain\Exception\StringTypeException;
use Domain\ValueObjectInterface;

trait StringTrait
{
    /** @var string */
    protected $string;

    public function __construct(string $string)
    {
        $this->string = $string;
    }

    public function isNull(): bool
    {
        return $this->string === null;
    }

    public function isSame(ValueObjectInterface $valueObject): bool
    {
        return $this->toPrimitive() === $valueObject->toPrimitive();
    }

    /**
     * @param string $primitive
     */
    public static function fromPrimitive($primitive): self
    {
        if (! is_string($primitive)) {
            throw new StringTypeException('Can only instantiate this object with a string.');
        }

        return new self($primitive);
    }

    public function toPrimitive(): string
    {
        return $this->string;
    }
}
