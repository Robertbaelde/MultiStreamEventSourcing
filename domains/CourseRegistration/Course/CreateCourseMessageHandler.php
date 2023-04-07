<?php

namespace Domains\CourseRegistration\Course;

use EventStream\DomainIdentifier;
use EventStream\Envelope;
use EventStream\Event;
use EventStream\EventQuery;
use EventStream\Message;
use EventStream\MessageHandler;
use EventStream\MessageTypeForId;
use EventStream\QueryResult;

class CreateCourseMessageHandler implements MessageHandler
{
    /**
     * @var array<Envelope>
     */
    private array $recordedEvents = [];

    private bool $courseCreated = false;

    public static function getEventQuery(CreateCourse $createCourse): EventQuery
    {
        return new EventQuery(
            new MessageTypeForId(CourseCreated::class, $createCourse->courseId)
        );
    }

    public static function reconstituteFrom(QueryResult $queryResult): static
    {
//        if(!$queryResult->eventQuery->equals(self::getEventQuery($createCourse))){
//            throw new \InvalidArgumentException('Invalid query');
//        }
        $course = new static();
        foreach ($queryResult->messages as $envelope){
            $methodName = 'apply' . (new \ReflectionClass($envelope->message))->getShortName();
            if (method_exists($course, $methodName)) {
                $course->$methodName($envelope->message, $envelope);
            }
        }
        return $course;
    }

    private function applyCourseCreated(CourseCreated $courseCreated, Envelope $envelope): void
    {
        $this->courseCreated = true;
    }

    public function createCourse(CreateCourse $command): void
    {
        if($this->courseCreated){
            throw SorryCantCreateCourse::courseAlreadyExists($command->courseId);
        }
        $this->recordThat(new CourseCreated(), $command->courseId);
        $this->recordThat(CourseNameChanged::toName($command->courseName), $command->courseId);
        $this->recordThat(CourseCapacityChanged::to($command->courseCapacity), $command->courseId);
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
