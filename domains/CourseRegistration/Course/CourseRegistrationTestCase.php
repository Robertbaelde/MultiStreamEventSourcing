<?php

namespace Domains\CourseRegistration\Course;

use EventStream\Envelope;
use EventStream\EventStore;
use EventStream\InMemoryEventStore;
use PHPUnit\Framework\TestCase;

abstract class CourseRegistrationTestCase extends TestCase
{
    public abstract function getHandlerClass(): string;

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

    protected function given(Envelope ...$events): self
    {
        $this->eventStore->recordEvents($events, null);
        return $this;
    }

    protected function when(object $command): self
    {
        $handler = $this->getHandlerClass();
        try {
            $query = $handler::getEventQuery($command);
            $queryResult = $this->eventStore->query($query);
            $commandHandler = $handler::reconstituteFrom($queryResult, $command);

            $commandHandler->handle($command);

            $this->recordedEvents = $commandHandler->releaseEvents();
            $this->eventStore->recordEvents($this->recordedEvents, $queryResult->getLock());
        } catch (\Exception $exception) {
            $this->thrownException = $exception;
        }

        return $this;
    }

    protected function then(Envelope ...$expectedEvents): void
    {
        $this->assertEquals($expectedEvents, $this->recordedEvents);
    }

    protected function thenExpectToFail(\Exception $expectedException): self
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
