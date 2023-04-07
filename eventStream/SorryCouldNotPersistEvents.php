<?php

namespace EventStream;

class SorryCouldNotPersistEvents extends \Exception
{
    public static function becauseStreamHasChangedSinceReading(LastExpectedInStream $lastExpectedInStream): self
    {
        return new self("Stream has changed since reading. Last expected in stream: {$lastExpectedInStream->domainIdentifier->toString()}:$lastExpectedInStream->eventType:$lastExpectedInStream->eventTypeCount");
    }
}
