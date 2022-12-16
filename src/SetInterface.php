<?php

declare(strict_types=1);

namespace Domain;

/**
 * @template T
 */
interface SetInterface extends ValueObjectInterface
{
    /**
     * @param T $valueObject
     */
    public function add(ValueObjectInterface $valueObject): self;

    /**
     * @param T $valueObject
     */
    public function remove(ValueObjectInterface $valueObject): self;

    /**
     * @param T $valueObject
     */
    public function contains(ValueObjectInterface $valueObject): bool;

    /**
     * @return array<T>
     */
    public function toArray(): array;
}
