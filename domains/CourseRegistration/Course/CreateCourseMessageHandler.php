<?php

namespace Domains\CourseRegistration\Course;

use EventStream\DomainIdentifier;
use EventStream\DomainIdentifiers;
use EventStream\Envelope;
use EventStream\Event;
use EventStream\EventQuery;
use EventStream\Message;
use EventStream\MessageHandler;

class CreateCourseMessageHandler implements MessageHandler
{
    /**
     * @var array<Envelope>
     */
    private array $recordedEvents = [];

    private function getMessageTypes(): array
    {
        return [
            CreateCourse::class,
        ];
    }

    public static function reconstituteFromEvents(Envelope ...$events): self
    {

    }

    public function handle(Message $message): void
    {
        if(!$message instanceof CreateCourse){
            throw new \InvalidArgumentException('Message must be of type ' . CreateCourse::class);
        }
        $this->createCourse($message);
    }

    private function createCourse(CreateCourse $message): void
    {
        $this->recordThat(new CourseCreated(), $message->courseId);
        $this->recordThat(CourseNameChanged::toName($message->courseName), $message->courseId);
        $this->recordThat(CourseCapacityChanged::to($message->courseCapacity), $message->courseId);
    }

    private function recordThat(Event $event, DomainIdentifier ...$domainIdentifiers): void
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
