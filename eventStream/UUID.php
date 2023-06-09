<?php

namespace EventStream;

abstract class UUID implements DomainIdentifier
{
    private function __construct(private string $id)
    {

    }

    public static function generate(): static
    {
        return new static(\Ramsey\Uuid\Uuid::uuid4()->toString());
    }
    public function toString(): string
    {
        return $this->id;
    }

    public static function fromString(string $id): static
    {
        return new static($id);
    }

    public function equals(DomainIdentifier $other): bool
    {
        return $this->toString() === $other->toString();
    }
}
