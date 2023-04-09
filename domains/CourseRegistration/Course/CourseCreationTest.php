<?php

namespace Domains\CourseRegistration\Course;

use EventStream\Envelope;

class CourseCreationTest extends CourseRegistrationTestCase
{
    public function getHandlerClass(): string
    {
        return CreateCourseMessageHandler::class;
    }

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

    /** @test */
    public function a_course_cant_be_created_twice()
    {
        $command = new CreateCourse(
            courseId: CourseId::generate(),
            courseName: 'Test Course',
            courseCapacity: 10
        );

        $this->given(
            Envelope::wrap(new CourseCreated(), $command->courseId),
        )
            ->when($command)
            ->thenExpectToFail(SorryCantCreateCourse::courseAlreadyExists($command->courseId));
    }
}
