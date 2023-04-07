<?php

namespace Domains\CourseRegistration\Course;

use EventStream\Message;
use EventStream\MessageHandler;

class CreateCourseMessageHandler implements MessageHandler
{

    public function __construct()
    {
    }

    public function handle(Message $message): void
    {
        // TODO: Implement handle() method.
    }
}
