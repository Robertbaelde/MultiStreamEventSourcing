<?php

namespace EventStream;

interface DomainIdentifier
{
    public function toString(): string;

    public static function fromString(string $string): DomainIdentifier;
}
