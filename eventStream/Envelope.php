<?php

namespace EventStream;

class Envelope
{
    public function __construct(
        public readonly Message $message,
        public readonly DomainIdentifiers $domainIdentifiers,
    )
    {

    }

    public static function wrap(Message $message, DomainIdentifier ...$domainIdentifiers): Envelope
    {
        return new Envelope($message, new DomainIdentifiers(...$domainIdentifiers));
    }
}
