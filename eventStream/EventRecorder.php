<?php

namespace EventStream;

class EventRecorder
{
    public function __construct(
        private EventStore $eventStore,
    ) {
    }

    public function recordEvents(Envelope ...$eventsToRecord): void
    {
        foreach ($eventsToRecord as $event) {
            $this->eventStore->recordEvent($event);
        }
    }
}
