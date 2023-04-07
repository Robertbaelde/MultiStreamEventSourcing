<?php

namespace EventStream;

class InMemoryEventStoreTest extends EventStoreTest
{

    public function eventStore(): EventStore
    {
        return new InMemoryEventStore();
    }
}
