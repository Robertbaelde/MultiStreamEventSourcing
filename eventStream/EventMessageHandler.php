<?php

namespace EventStream;

use Domains\CourseRegistration\Course\CreateCourse;

abstract class EventMessageHandler
{
    /**
     * @var array<Envelope>
     */
    private array $recordedEvents = [];

    abstract public static function getEventQuery(object $command): EventQuery;

    abstract public function handle(object $command): void;

    public static function reconstituteFrom(QueryResult $queryResult, ?object $command = null): static
    {
//        if(!$queryResult->eventQuery->equals(self::getEventQuery($createCourse))){
//            throw new \InvalidArgumentException('Invalid query');
//        }
        $self = new static();

        if($command !== null && method_exists($self, 'withCommandContext')){
            $self->withCommandContext($command);
        }

        foreach ($queryResult->messages as $envelope){
            $methodName = 'apply' . (new \ReflectionClass($envelope->message))->getShortName();
            if (method_exists($self, $methodName)) {
                $self->$methodName($envelope->message, $envelope);
            }
        }

        return $self;
    }

    protected function recordThat(Event $event, DomainIdentifier ...$domainIdentifiers): void
    {
        $this->recordedEvents[] = Envelope::wrap($event, ...$domainIdentifiers);
    }

    /**
     * @return Envelope[]
     */
    public function releaseEvents(): array
    {
        $releasedEvents = $this->recordedEvents;
        $this->recordedEvents = [];
        return $releasedEvents;
    }
}
