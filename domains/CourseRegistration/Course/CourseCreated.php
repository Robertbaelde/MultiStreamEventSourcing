<?php

namespace Domains\CourseRegistration\Course;

use EventStream\Event;

class CourseCreated implements Event
{
    public function __construct(
    ) {
    }
}
