<?php

namespace Domains\CourseRegistration\Course;

use EventStream\DomainIdentifier;

abstract class UUID implements DomainIdentifier
{
    private function __construct(private string $id)
    {

    }

    public static function generate(): DomainIdentifier
    {
        return new static(\Ramsey\Uuid\Uuid::uuid4()->toString());
    }
    public function toString(): string
    {
        return $this->id;
    }

    public static function fromString(string $id): DomainIdentifier
    {
        return new static($id);
    }
}
