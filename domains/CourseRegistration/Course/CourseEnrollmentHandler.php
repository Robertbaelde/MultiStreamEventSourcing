<?php

namespace Domains\CourseRegistration\Course;

use Domains\CourseRegistration\Student\StudentCourseEnrollmentLimitChanged;
use Domains\CourseRegistration\Student\StudentId;
use EventStream\Envelope;
use EventStream\EventMessageHandler;
use EventStream\EventQuery;
use EventStream\MessageHandler;
use EventStream\MessageTypeForId;
use InvalidArgumentException;

class CourseEnrollmentHandler extends EventMessageHandler implements MessageHandler
{
    private CourseId $courseToEnrollIn;
    private StudentId $studentToEnroll;
    private int $courseEnrollmentCount = 0;
    private int $studentEnrollmentCount = 0;
    private bool $studentEnrolledInCourse = false;
    private int $courseCapacity = 0;
    private int $studentCourseEnrollmentLimit = 0;

    public static function getEventQuery(object $command): EventQuery
    {
        if(!$command instanceof EnrollInCourse){
            throw new InvalidArgumentException('Invalid command');
        }
        return new EventQuery(
            new MessageTypeForId(StudentCourseEnrollmentLimitChanged::class, $command->studentId),
            new MessageTypeForId(CourseCapacityChanged::class, $command->courseId),
            new MessageTypeForId( StudentEnrolledInCourse::class, $command->studentId),
            new MessageTypeForId( StudentEnrolledInCourse::class, $command->courseId),
        );
    }

    public function withCommandContext(object $command): void
    {
        if(!$command instanceof EnrollInCourse){
            throw new InvalidArgumentException('Invalid command');
        }
        $this->courseToEnrollIn = $command->courseId;
        $this->studentToEnroll = $command->studentId;
    }

    public function applyStudentCourseEnrollmentLimitChanged(StudentCourseEnrollmentLimitChanged $studentCourseEnrollmentLimitChanged, Envelope $envelope): void
    {
        $this->studentCourseEnrollmentLimit = $studentCourseEnrollmentLimitChanged->limit;
    }

    public function applyCourseCapacityChanged(CourseCapacityChanged $courseCapacityChanged, Envelope $envelope): void
    {
        $this->courseCapacity = $courseCapacityChanged->courseCapacity;
    }

    protected function applyStudentEnrolledInCourse(StudentEnrolledInCourse $studentEnrolledInCourse, Envelope $envelope): void
    {
        if($envelope->domainIdentifiers->contains($this->courseToEnrollIn)){
            $this->courseEnrollmentCount++;
        }

        if($envelope->domainIdentifiers->contains($this->studentToEnroll)){
            $this->studentEnrollmentCount++;
        }

        if($envelope->domainIdentifiers->contains($this->courseToEnrollIn) && $envelope->domainIdentifiers->contains($this->studentToEnroll)){
            $this->studentEnrolledInCourse = true;
        }
    }

    public function handle(object $command): void
    {
        if(!$command instanceof EnrollInCourse){
            throw new InvalidArgumentException('Invalid command');
        }

        if($this->studentEnrolledInCourse){
            throw SorryCantEnrollStudent::studentAlreadyEnrolled($command->studentId, $command->courseId);
        }

        if($this->courseCapacity <= $this->courseEnrollmentCount){
            throw SorryCantEnrollStudent::courseIsFull($command->studentId, $command->courseId);
        }

        if($this->studentCourseEnrollmentLimit <= $this->studentEnrollmentCount){
            throw SorryCantEnrollStudent::studentReachedItsEnrollmentLimit($command->studentId, $command->courseId);
        }

        $this->recordThat(new StudentEnrolledInCourse(), $command->courseId, $command->studentId);
    }
}
