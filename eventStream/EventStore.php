<?php

namespace EventStream;

interface EventStore
{
    public function recordEvents(array $events, ?OptimisticLock $optimisticLock): void;

    public function query(EventQuery $eventQuery): QueryResult;
}
