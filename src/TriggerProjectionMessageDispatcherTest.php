<?php

namespace Robertbaelde\ProjectionEngine;

use EventSauce\EventSourcing\AggregateRootId;
use EventSauce\EventSourcing\Header;
use EventSauce\EventSourcing\Message;
use PHPUnit\Framework\TestCase;
use Robertbaelde\ProjectionEngine\Stubs\AggregateRootIdStub;
use Robertbaelde\ProjectionEngine\Stubs\EventStub;

class TriggerProjectionMessageDispatcherTest extends TestCase
{
    /** @test */
    public function it_triggers_the_projectionEngine_once()
    {
        $aggregateRootId = AggregateRootIdStub::fromString('test');
        $projectionEngineStub = new StubProjectionEngine();
        $dispatcher = new TriggerProjectionMessageDispatcher($projectionEngineStub);
        $message = new Message(new EventStub('test'));
        $message = $message->withHeaders([
            Header::AGGREGATE_ROOT_ID => AggregateRootIdStub::fromString('test'),
            Header::AGGREGATE_ROOT_VERSION => 1
        ]);
        $dispatcher->dispatch($message);

        $this->assertEquals(1, $projectionEngineStub->callsForAggregate($aggregateRootId));
    }

    /** @test */
    public function it_fetches_the_aggregate_id_even_if_its_not_present_on_the_first_message()
    {
        $aggregateRootId = AggregateRootIdStub::fromString('test');
        $projectionEngineStub = new StubProjectionEngine();
        $dispatcher = new TriggerProjectionMessageDispatcher($projectionEngineStub);

        $message = new Message(new EventStub('test'));
        $message = $message->withHeaders([
            Header::AGGREGATE_ROOT_ID => AggregateRootIdStub::fromString('test'),
            Header::AGGREGATE_ROOT_VERSION => 1
        ]);
        $dispatcher->dispatch(
            new Message(new EventStub('foo')),
            $message
        );

        $this->assertEquals(1, $projectionEngineStub->callsForAggregate($aggregateRootId));
    }
}

class StubProjectionEngine implements ProjectionEngineInterface
{
    private array $aggregateCalls = [];

    public function __construct()
    {

    }

    public function processMessageForAggregate(AggregateRootId $aggregateRootId): void
    {
        if(!array_key_exists($aggregateRootId->toString(), $this->aggregateCalls)){
            $this->aggregateCalls[$aggregateRootId->toString()] = 0;
        }
        $this->aggregateCalls[$aggregateRootId->toString()]++;
    }

    public function startReplayForAggregate(AggregateRootId $aggregateRootId): void
    {
    }

    public function callsForAggregate(AggregateRootId $aggregateRootId): int
    {
        return $this->aggregateCalls[$aggregateRootId->toString()] ?? 0;
    }
}
