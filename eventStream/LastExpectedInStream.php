<?php

namespace EventStream;

class LastExpectedInStream
{
    public function __construct(
        public readonly DomainIdentifier $domainIdentifier,
        public readonly string $eventType,
        public readonly ?EventId $eventId,
    )
    {

    }
}
