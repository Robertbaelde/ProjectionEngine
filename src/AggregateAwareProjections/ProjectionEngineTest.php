<?php

declare(strict_types=1);

namespace Robertbaelde\ProjectionEngine\AggregateAwareProjections;

use EventSauce\EventSourcing\Header;
use EventSauce\EventSourcing\Message;
use EventSauce\EventSourcing\SynchronousMessageDispatcher;
use PHPUnit\Framework\TestCase;
use Robertbaelde\ProjectionEngine\AggregateAwareProjections\Stubs\AggregateRootIdStub;
use Robertbaelde\ProjectionEngine\AggregateAwareProjections\Stubs\EventConsumerStub;
use Robertbaelde\ProjectionEngine\AggregateAwareProjections\Stubs\EventStub;
use Robertbaelde\ProjectionEngine\AggregateAwareProjections\Stubs\InMemoryProjectionEngineStateRepository;
use Robertbaelde\ProjectionEngine\AggregateAwareProjections\Stubs\InMemoryReplayMessageRepository;
use Robertbaelde\ProjectionEngine\AggregateAwareProjections\Stubs\MessageDispatcherThatResetsState;

class ProjectionEngineTest extends TestCase
{
    private InMemoryReplayMessageRepository $messageRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->messageRepository = new InMemoryReplayMessageRepository();
    }

    /** @test */
    public function on_event_it_retrieves_all_events_for_aggregate_since_last_offset_and_applies_them(): void
    {
        $aggregateRootId = AggregateRootIdStub::fromString('aggregate_1');
        $this->generateStubEvents($aggregateRootId, 10);

        $otherAggregateId = AggregateRootIdStub::fromString('aggregate_2');
        $this->generateStubEvents($otherAggregateId, 10);

        $projectionEngineInMemoryRepo = new InMemoryProjectionEngineStateRepository('test-consumer');

        $eventConsumer = new EventConsumerStub();

        $projectionEngine = new ProjectionEngine(
            $this->messageRepository,
            $projectionEngineInMemoryRepo,
            $projectionEngineInMemoryRepo,
            new SynchronousMessageDispatcher($eventConsumer));

        $message = $this->makeStubMessage($aggregateRootId, 11);
        $this->messageRepository->persist($message);

        $projectionEngine->processMessageForAggregate($aggregateRootId);

        $this->assertCount(11, $eventConsumer->getHandledMessages());
        $this->assertTrue($projectionEngineInMemoryRepo->lockWasObtained());
        $this->assertFalse($projectionEngineInMemoryRepo->isLocked($aggregateRootId->toString()));

        $this->assertEquals(11, $projectionEngineInMemoryRepo->getProjectorOffsetForAggregate($aggregateRootId->toString()));
    }

    /** @test */
    public function it_starts_from_the_previous_stored_offset(): void
    {
        $aggregateRootId = AggregateRootIdStub::fromString('aggregate_1');
        $this->generateStubEvents($aggregateRootId, 10);

        $projectionEngineInMemoryRepo = new InMemoryProjectionEngineStateRepository('test-consumer');
        $projectionEngineInMemoryRepo->storeOffset($aggregateRootId->toString(), 5);

        $eventConsumer = new EventConsumerStub();

        $projectionEngine = new ProjectionEngine(
            $this->messageRepository,
            $projectionEngineInMemoryRepo,
            $projectionEngineInMemoryRepo,
            new SynchronousMessageDispatcher($eventConsumer)
        );

        $projectionEngine->processMessageForAggregate($aggregateRootId);

        $this->assertCount(5, $eventConsumer->getHandledMessages());
        $this->assertEquals('6', $eventConsumer->getHandledMessages()[0]->event()->value);
        $this->assertEquals(10, $projectionEngineInMemoryRepo->getProjectorOffsetForAggregate($aggregateRootId->toString()));
    }

    /** @test */
    public function it_resets_the_offset_on_replay(): void
    {
        $aggregateRootId = AggregateRootIdStub::fromString('aggregate_1');
        $this->generateStubEvents($aggregateRootId, 10);

        $projectionEngineInMemoryRepo = new InMemoryProjectionEngineStateRepository('test-consumer');
        $projectionEngineInMemoryRepo->storeOffset($aggregateRootId->toString(), 5);

        $eventConsumer = new EventConsumerStub();

        $projectionEngine = new ProjectionEngine(
            $this->messageRepository,
            $projectionEngineInMemoryRepo,
            $projectionEngineInMemoryRepo,
            new SynchronousMessageDispatcher($eventConsumer),
            10
        );

        $projectionEngine->startReplayForAggregate($aggregateRootId);

        $this->assertCount(10, $eventConsumer->getHandledMessages());
        $this->assertEquals('1', $eventConsumer->getHandledMessages()[0]->event()->value);
        $this->assertEquals(10, $projectionEngineInMemoryRepo->getProjectorOffsetForAggregate($aggregateRootId->toString()));
    }

    /** @test */
    public function when_the_consumer_implements_the_resets_state_before_replay_interface_it_gets_called_to_reset_state_on_replay(): void
    {
        $aggregateRootId = AggregateRootIdStub::fromString('aggregate_1');
        $messageRepository = new InMemoryReplayMessageRepository();
        $projectionEngineInMemoryRepo = new InMemoryProjectionEngineStateRepository('test-consumer');
        $dispatcher = new MessageDispatcherThatResetsState(new EventConsumerStub());

        $projectionEngine = new ProjectionEngine(
            $messageRepository,
            $projectionEngineInMemoryRepo,
            $projectionEngineInMemoryRepo,
            $dispatcher
        );

        $projectionEngine->startReplayForAggregate($aggregateRootId);
        $this->assertTrue($dispatcher->wasReset());
    }

    private function generateStubEvents(\EventSauce\EventSourcing\AggregateRootId $aggregateRootId, int $numberOfMessages = 1)
    {
        $this->messageRepository->persist(
            ...array_map(
                fn($number) => $this->makeStubMessage($aggregateRootId, $number),
                range(1, $numberOfMessages)
            )
        );
    }

    private function makeStubMessage(\EventSauce\EventSourcing\AggregateRootId $aggregateRootId, int $version): Message
    {
        $message = new Message(new EventStub((string) $version));
        return $message->withHeaders([
            Header::AGGREGATE_ROOT_ID => $aggregateRootId,
            Header::AGGREGATE_ROOT_VERSION => $version
        ]);
    }
}
