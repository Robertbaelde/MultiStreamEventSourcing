<?php

namespace Domains\CourseRegistration\Course;

use EventStream\DomainIdentifiers;
use EventStream\Envelope;
use PHPUnit\Framework\TestCase;

class CourseCreationTest extends TestCase
{
    /** @test */
    public function a_course_can_be_created()
    {
        $command = new CreateCourse(
            courseId: CourseId::generate(),
            courseName: 'Test Course',
            courseCapacity: 10
        );

        $this->given()
            ->when($command)
            ->then(
                Envelope::wrap(new CourseCreated(), $command->courseId),
                Envelope::wrap(CourseNameChanged::toName($command->courseName), $command->courseId),
                Envelope::wrap(CourseCapacityChanged::to($command->courseCapacity), $command->courseId),
            );

    }

    private function given(): self
    {
        return $this;
    }

    private function when(CreateCourse $command): self
    {
        $messageHandler = new CreateCourseMessageHandler();
        $messageHandler->handle($command);
        return $this;
    }

    private function then(Envelope ...$expectedEvents): void
    {

    }
}
