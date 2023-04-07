<?php

namespace Domains\CourseRegistration\Course;

class SorryCantCreateCourse extends \DomainException
{

    public static function courseAlreadyExists(CourseId $courseId): self
    {
        return new self("Course with id: {$courseId->toString()} already exists");
    }
}
