<?php

namespace EventStream;

class QueryResult
{
    /**
     * @var Envelope[]
     */
    public readonly array $messages;

    public function __construct(
        public readonly EventQuery $eventQuery,
        Envelope ...$messages
    ) {
        $this->messages = $messages;
    }

    public function getLastEventId(): EventId
    {
        $messages = $this->messages;
        return end($messages)->eventId;
    }

    public function getLock(): OptimisticLock
    {
        $locks = [];
        // build locks
        foreach($this->eventQuery->messageTypes as $messageType){
            $locks[] = new LastExpectedInStream($messageType->domainIdentifier, $messageType->messageType, $this->getLastEventIdFor($messageType->domainIdentifier, $messageType->messageType));
        }
        return new OptimisticLock(...$locks);
    }

    private function getLastEventIdFor(DomainIdentifier $domainIdentifier, string $messageType): ?EventId
    {
        $lastId = null;
        foreach ($this->messages as $message) {
            if ($message->domainIdentifiers->contains($domainIdentifier) && $message->message instanceof $messageType) {
                $lastId = $message->eventId;
            }
        }
        return $lastId;
    }
}
