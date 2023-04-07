<?php

namespace EventStream;

class InMemoryEventStore implements EventStore
{
    private array $recordedMessages = [];

    public function recordEvents(array $events, ?OptimisticLock $optimisticLock): void
    {

        // assert lock (in db imp, should be transactional)
        if($optimisticLock !== null){
            foreach($optimisticLock->lastExpectedInStream as $lastExpectedInStream){
                $messagesInStream = array_filter($this->recordedMessages, function (Envelope $envelope) use ($lastExpectedInStream) {
                    return $envelope->message instanceof $lastExpectedInStream->eventType && $envelope->domainIdentifiers->contains($lastExpectedInStream->domainIdentifier);
                });
                $lastInStream = end($messagesInStream);
                if(!$lastInStream->eventId->equals($lastExpectedInStream->eventId)){
                    throw SorryCouldNotPersistEvents::becauseStreamHasChangedSinceReading($lastExpectedInStream);
                }
            }
        }
        foreach ($events as $event) {
            if(!$event instanceof Envelope) {
                throw new \InvalidArgumentException('Event must be an instance of Envelope');
            }
        }
        foreach ($events as $event) {
            $this->recordedMessages[] = $event;
        }
    }

    public function query(EventQuery $eventQuery): QueryResult
    {
        $eventsToReturn = [];
        foreach ($eventQuery->messageTypes as $messageType) {
            $eventsToReturn = array_merge(array_filter($this->recordedMessages, function (Envelope $envelope) use ($messageType) {
                return $envelope->message instanceof $messageType->messageType && $envelope->domainIdentifiers->contains($messageType->domainIdentifier);
            }), $eventsToReturn);
        }

        $events = [];

        // deduplicate
        foreach ($eventsToReturn as $event) {
            $events[$event->eventId->toString()] = $event;
        }


        return new QueryResult($eventQuery, ...array_values($events));
    }
}
