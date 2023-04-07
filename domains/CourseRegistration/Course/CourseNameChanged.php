<?php

namespace Domains\CourseRegistration\Course;

use EventStream\Event;

class CourseNameChanged implements Event
{

    public function __construct(
        public readonly string $courseName
    ) {
    }

    public static function toName(string $courseName): static
    {
        return new self($courseName);
    }
}
