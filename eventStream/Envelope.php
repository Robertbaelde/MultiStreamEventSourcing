<?php

namespace EventStream;

class Envelope
{
    public function __construct(
        public readonly Message $message,
        public readonly DomainIdentifiers $domainIdentifiers,
        public readonly ?EventId $eventId = null,
    )
    {
    }

    public static function wrap(Message $message, DomainIdentifier ...$domainIdentifiers): Envelope
    {
        return new Envelope( $message, new DomainIdentifiers(...$domainIdentifiers));
    }

    public function withEventId(EventId $eventId): Envelope
    {
        return new Envelope($this->message, $this->domainIdentifiers, $eventId);
    }
}
