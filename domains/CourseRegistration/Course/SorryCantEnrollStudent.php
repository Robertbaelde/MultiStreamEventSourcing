<?php

namespace Domains\CourseRegistration\Course;

use Domains\CourseRegistration\Student\StudentId;
use Exception;

class SorryCantEnrollStudent extends Exception
{

    public static function studentAlreadyEnrolled(StudentId $studentId, CourseId $courseId): self
    {
        return new self("Student {$studentId->toString()} is already enrolled in course {$courseId->toString()}");
    }

    public static function courseIsFull(StudentId $studentId, CourseId $courseId): self
    {
        return new self("Course {$courseId->toString()} has reached capacity, student {$studentId->toString()} can't enroll");
    }

    public static function studentReachedItsEnrollmentLimit(StudentId $studentId, CourseId $courseId): self
    {
        return new self("Student {$studentId->toString()} has reached its enrollment limit, can't enroll in course {$courseId->toString()}");
    }
}
