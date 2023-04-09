<?php

namespace Domains\CourseRegistration\Student;

use EventStream\Event;

final class StudentCourseEnrollmentLimitChanged implements Event
{
    public function __construct(
        public readonly int $limit,
    )
    {
    }
}
