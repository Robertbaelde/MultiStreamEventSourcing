<?php

namespace Domains\CourseRegistration\Course;

use EventStream\Command;

class CreateCourse implements Command
{

    public function __construct(
        public readonly CourseId $courseId,
        public readonly string $courseName,
        public readonly int $courseCapacity,
    )
    {
    }
}
