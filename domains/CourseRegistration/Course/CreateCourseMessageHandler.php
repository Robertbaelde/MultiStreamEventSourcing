<?php

namespace Domains\CourseRegistration\Course;

use EventStream\Envelope;
use EventStream\EventMessageHandler;
use EventStream\EventQuery;
use EventStream\MessageHandler;
use EventStream\MessageTypeForId;

final class CreateCourseMessageHandler extends EventMessageHandler implements MessageHandler
{
    private bool $courseCreated = false;

    public static function getEventQuery(object $command): EventQuery
    {
        if(!$command instanceof CreateCourse){
            throw new \InvalidArgumentException('Invalid command');
        }
        return new EventQuery(
            new MessageTypeForId(CourseCreated::class, $command->courseId)
        );
    }

    protected function applyCourseCreated(CourseCreated $courseCreated, Envelope $envelope): void
    {
        $this->courseCreated = true;
    }

    public function handle(object $command): void
    {
        if(!$command instanceof CreateCourse){
            throw new \InvalidArgumentException('Invalid command');
        }

        if($this->courseCreated){
            throw SorryCantCreateCourse::courseAlreadyExists($command->courseId);
        }
        $this->recordThat(new CourseCreated(), $command->courseId);
        $this->recordThat(CourseNameChanged::toName($command->courseName), $command->courseId);
        $this->recordThat(CourseCapacityChanged::to($command->courseCapacity), $command->courseId);
    }
}
