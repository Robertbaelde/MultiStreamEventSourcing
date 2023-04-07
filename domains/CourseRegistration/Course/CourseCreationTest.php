<?php

namespace Domains\CourseRegistration\Course;

use EventStream\Envelope;
use EventStream\EventRecorder;
use EventStream\EventStore;
use EventStream\InMemoryEventStore;
use PHPUnit\Framework\TestCase;

class CourseCreationTest extends TestCase
{
    private EventStore $eventStore;
    private ?\Exception $expectedException = null;
    private ?\Exception $thrownException = null;
    /**
     * @var Envelope[]
     */
    private array $recordedEvents = [];

    public function setUp(): void
    {
        $this->eventStore = new InMemoryEventStore();
    }

    /** @test */
    public function a_course_can_be_created()
    {
        $command = new CreateCourse(
            courseId: CourseId::generate(),
            courseName: 'Test Course',
            courseCapacity: 10
        );

        $this->given()
            ->when($command)
            ->then(
                Envelope::wrap(new CourseCreated(), $command->courseId),
                Envelope::wrap(CourseNameChanged::toName($command->courseName), $command->courseId),
                Envelope::wrap(CourseCapacityChanged::to($command->courseCapacity), $command->courseId),
            );
    }

    /** @test */
    public function a_course_cant_be_created_twice()
    {
        $command = new CreateCourse(
            courseId: CourseId::generate(),
            courseName: 'Test Course',
            courseCapacity: 10
        );

        $this->given(
            Envelope::wrap(new CourseCreated(), $command->courseId),
        )
            ->when($command)
            ->thenExpectToFail(SorryCantCreateCourse::courseAlreadyExists($command->courseId));
    }

    private function given(Envelope ...$events): self
    {
        $this->eventStore->recordEvents($events, null);
        return $this;
    }

    private function when(CreateCourse $command): self
    {
        try {
            $query = CreateCourseMessageHandler::getEventQuery($command);
            $queryResult = $this->eventStore->query($query);
            $commandHandler = CreateCourseMessageHandler::reconstituteFrom($queryResult);
            $commandHandler->createCourse($command);
            $this->recordedEvents = $commandHandler->releaseEvents();
            $this->eventStore->recordEvents($this->recordedEvents, $queryResult->getLock());
        } catch (\Exception $exception) {
            $this->thrownException = $exception;
        }

        return $this;
    }

    private function then(Envelope ...$expectedEvents): void
    {
        $this->assertEquals($expectedEvents, $this->recordedEvents);
    }

    private function thenExpectToFail(\Exception $expectedException): self
    {
        $this->expectedException = $expectedException;
        return $this;
    }

    /**
     * @after
     */
    protected function assertScenario(): void
    {
        if($this->thrownException !== null && $this->expectedException === null){
            throw $this->thrownException;
        }

        if ($this->expectedException !== null) {
            $this->assertNotNull($this->thrownException, "Expected exception " . get_class($this->expectedException). " to be thrown, but it wasn't");
            $this->assertInstanceOf(get_class($this->expectedException), $this->thrownException);
            $this->assertEquals($this->expectedException->getMessage(), $this->thrownException->getMessage());
        }
    }
}
