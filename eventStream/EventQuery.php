<?php

namespace EventStream;

class EventQuery
{
    /**
     * @var MessageTypeForId[]
     */
    public readonly array $messageTypes;

    public function __construct(
        MessageTypeForId ...$messageTypes,
    )
    {
        $this->messageTypes = $messageTypes;
    }
}
