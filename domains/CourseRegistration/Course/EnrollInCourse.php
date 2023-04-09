<?php

namespace Domains\CourseRegistration\Course;

use Domains\CourseRegistration\Student\StudentId;

class EnrollInCourse
{
    public function __construct(
        public readonly CourseId $courseId,
        public readonly StudentId $studentId,
    )
    {

    }
}
