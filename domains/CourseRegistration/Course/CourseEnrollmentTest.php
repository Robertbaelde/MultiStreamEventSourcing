<?php

namespace Domains\CourseRegistration\Course;

use Domains\CourseRegistration\Student\StudentCourseEnrollmentLimitChanged;
use Domains\CourseRegistration\Student\StudentId;
use EventStream\Envelope;

class CourseEnrollmentTest extends CourseRegistrationTestCase
{

    public function getHandlerClass(): string
    {
        return CourseEnrollmentHandler::class;
    }

    /** @test */
    public function a_student_can_enroll_in_a_course()
    {
        $courseId = CourseId::generate();
        $studentId = StudentId::generate();
        $this->given(
            Envelope::wrap(new StudentCourseEnrollmentLimitChanged(1), $studentId),
            Envelope::wrap(new CourseCapacityChanged(5), $courseId)
        )->when(
            new EnrollInCourse(
                courseId: $courseId,
                studentId: $studentId,
            )
        )->then(
            Envelope::wrap(new StudentEnrolledInCourse(), $courseId, $studentId),
        );
    }

    /** @test */
    public function a_student_cant_enroll_in_the_same_course_twice()
    {
        $courseId = CourseId::generate();
        $studentId = StudentId::generate();
        $this->given(
            Envelope::wrap(new CourseCapacityChanged(5), $courseId),
            Envelope::wrap(new StudentEnrolledInCourse(), $courseId, $studentId),
        )->when(
            new EnrollInCourse(
                courseId: $courseId,
                studentId: $studentId,
            )
        )->thenExpectToFail(SorryCantEnrollStudent::studentAlreadyEnrolled($studentId, $courseId));
    }

    /** @test */
    public function it_wont_enroll_a_student_when_course_has_reached_capacity()
    {
        $courseId = CourseId::generate();
        $studentId = StudentId::generate();
        $this->given(
            Envelope::wrap(new CourseCapacityChanged(2), $courseId),
            Envelope::wrap(new StudentEnrolledInCourse(), $courseId, StudentId::generate()),
            Envelope::wrap(new StudentEnrolledInCourse(), $courseId, StudentId::generate()),
        )->when(
            new EnrollInCourse(
                courseId: $courseId,
                studentId: $studentId,
            )
        )->thenExpectToFail(SorryCantEnrollStudent::courseIsFull($studentId, $courseId));
    }

    /** @test */
    public function it_wont_enroll_a_student_when_student_max_course_limit_was_reached()
    {
        $courseId = CourseId::generate();
        $studentId = StudentId::generate();
        $this->given(
            Envelope::wrap(new CourseCapacityChanged(2), $courseId),
            Envelope::wrap(new StudentCourseEnrollmentLimitChanged(1), $studentId),
            Envelope::wrap(new StudentEnrolledInCourse(), CourseId::generate(), $studentId),
        )->when(
            new EnrollInCourse(
                courseId: $courseId,
                studentId: $studentId,
            )
        )->thenExpectToFail(SorryCantEnrollStudent::studentReachedItsEnrollmentLimit($studentId, $courseId));
    }
}
