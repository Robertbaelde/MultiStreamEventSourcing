<?php

namespace EventStream;

class Envelope
{
    public function __construct(
        public readonly EventId $eventId,
        public readonly Message $message,
        public readonly DomainIdentifiers $domainIdentifiers,
    )
    {
    }

    public static function wrap(Message $message, DomainIdentifier ...$domainIdentifiers): Envelope
    {
        return new Envelope(EventId::generate(), $message, new DomainIdentifiers(...$domainIdentifiers));
    }
}
