<?php

namespace Domains\CourseRegistration\Course;

use EventStream\Event;

class CourseCapacityChanged implements Event
{
    public function __construct(
        public readonly int $courseCapacity
    ) {
    }

    public static function to(int $courseCapacity): self
    {
        return new self($courseCapacity);
    }
}
