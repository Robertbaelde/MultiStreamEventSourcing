<?php

namespace EventStream;

class MessageTypeForId
{
    public function __construct(
        public readonly string $messageType,
        public readonly DomainIdentifier $domainIdentifier,
    ) {
    }
}
