<?php

namespace EventStream;

use EventStream\Stubs\EventA;
use EventStream\Stubs\EventB;
use EventStream\Stubs\IdentifierA;
use EventStream\Stubs\IdentifierB;
use PHPUnit\Framework\TestCase;

abstract class EventStoreTest extends TestCase
{
    public abstract function eventStore(): EventStore;

    /** @test */
    public function it_can_store_a_event_with_one_identifier()
    {
        $idA = IdentifierA::generate();
        $eventQuery = new EventQuery(
            new MessageTypeForId(EventA::class, $idA),
        );
        $eventStore = $this->eventStore();
        $eventStore->recordEvents( [Envelope::wrap(new EventA(), $idA)], null);

        $queryResult = $eventStore->query($eventQuery);
        $this->assertCount(1, $queryResult->messages);
        $this->assertTrue($queryResult->messages[0]->domainIdentifiers->contains($idA));
    }

    /** @test */
    public function it_can_store_a_event_with_two_identifiers()
    {
        $idA = IdentifierA::generate();
        $idB = IdentifierB::generate();

        $eventStore = $this->eventStore();
        $eventStore->recordEvents( [Envelope::wrap(new EventA(), $idA, $idB)], null);

        $queryResult = $eventStore->query(new EventQuery(
            new MessageTypeForId(EventA::class, $idA),
            new MessageTypeForId(EventA::class, $idB),
        ));
        $this->assertCount(1, $queryResult->messages);

        $queryResult = $eventStore->query(new EventQuery(
            new MessageTypeForId(EventA::class, $idA),
        ));
        $this->assertCount(1, $queryResult->messages);

        $queryResult = $eventStore->query(new EventQuery(
            new MessageTypeForId(EventA::class, $idB),
        ));
        $this->assertCount(1, $queryResult->messages);
    }

    /** @test */
    public function event_query_filters_on_event_type()
    {
        $eventStore = $this->eventStore();

        $idA = IdentifierA::generate();
        $eventStore->recordEvents( [Envelope::wrap(new EventA(), $idA)], null);

        $result = $eventStore->query(new EventQuery(
            new MessageTypeForId(EventB::class, $idA),
        ));

        $this->assertCount(0, $result->messages);
    }

    /** @test */
    public function event_query_filters_on_event_id()
    {
        $eventStore = $this->eventStore();

        $idA = IdentifierA::generate();
        $eventStore->recordEvents( [Envelope::wrap(new EventA(), $idA)], null);

        $result = $eventStore->query(new EventQuery(
            new MessageTypeForId(EventA::class, IdentifierA::generate()),
        ));

        $this->assertCount(0, $result->messages);
    }

    /** @test */
    public function event_query_filters_on_event_id_type()
    {
        $eventStore = $this->eventStore();

        $idA = IdentifierA::generate();
        $idB = IdentifierB::fromString($idA->toString());
        $eventStore->recordEvents( [Envelope::wrap(new EventA(), $idA)], null);

        $result = $eventStore->query(new EventQuery(
            new MessageTypeForId(EventA::class, $idB),
        ));

        $this->assertCount(0, $result->messages);
    }

    /** @test */
    public function it_uses_optimistic_locking_on_event_type_id_level()
    {
        $eventStore = $this->eventStore();

        $idA = IdentifierA::generate();
        $eventQuery = new EventQuery(
            new MessageTypeForId(EventA::class, $idA),
        );
        $eventStore->recordEvents( [Envelope::wrap(new EventA(), $idA)], null);

        $queryResult = $eventStore->query($eventQuery);

        // this one should work
        $eventStore->recordEvents( [Envelope::wrap(new EventA(), $idA)], null);

        $exceptionThrown = false;
        // this one should throw an exception
        try {
            $eventStore->recordEvents( [Envelope::wrap(new EventA(), $idA)], $queryResult->getLock());
        } catch (SorryCouldNotPersistEvents $e) {
            $exceptionThrown = true;
        }
        $this->assertTrue($exceptionThrown, 'Expected exception to be thrown');
    }

    /** @test */
    public function it_doesnt_record_anything_when_lock_fails()
    {
        $eventStore = $this->eventStore();

        $idA = IdentifierA::generate();
        $eventQuery = new EventQuery(
            new MessageTypeForId(EventA::class, $idA),
        );
        $eventStore->recordEvents( [Envelope::wrap(new EventA(), $idA)], null);

        $queryResult = $eventStore->query($eventQuery);

        // this one should work
        $eventStore->recordEvents( [Envelope::wrap(new EventA(), $idA)], null);

        $exceptionThrown = false;
        // this one should throw an exception
        try {
            $eventStore->recordEvents( [
                Envelope::wrap(new EventA(), $idA),
                Envelope::wrap(new EventB(), $idA),
            ], $queryResult->getLock());
        } catch (SorryCouldNotPersistEvents $e) {
            $exceptionThrown = true;
        }
        $this->assertTrue($exceptionThrown, 'Expected exception to be thrown');

        $queryResult = $eventStore->query($eventQuery);
        $this->assertCount(2, $queryResult->messages);
    }
}
