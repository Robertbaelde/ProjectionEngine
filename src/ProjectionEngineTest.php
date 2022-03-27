<?php

declare(strict_types=1);

namespace Robertbaelde\ProjectionEngine;

use EventSauce\EventSourcing\Message;
use EventSauce\EventSourcing\SynchronousMessageDispatcher;
use PHPUnit\Framework\TestCase;
use Robertbaelde\ProjectionEngine\Stubs\EventConsumerStub;
use Robertbaelde\ProjectionEngine\Stubs\EventStub;
use Robertbaelde\ProjectionEngine\Stubs\InMemoryProjectionEngineStateRepository;
use Robertbaelde\ProjectionEngine\Stubs\InMemoryReplayMessageRepository;
use Robertbaelde\ProjectionEngine\Stubs\MessageDispatcherThatResetsState;

class ProjectionEngineTest extends TestCase
{
    /** @test */
    public function on_event_it_retrieves_all_events_since_last_offset_and_applies_them(): void
    {
        $messageRepository = new InMemoryReplayMessageRepository();
        $messageRepository->persist(
            ...array_map(
            fn ($number) => new Message(new EventStub((string) $number)),
            range(1, 10)
        )
        );

        $projectionEngineInMemoryRepo = new InMemoryProjectionEngineStateRepository('test-consumer');

        $eventConsumer = new EventConsumerStub();

        $projectionEngine = new ProjectionEngine(
            $messageRepository,
            $projectionEngineInMemoryRepo,
            $projectionEngineInMemoryRepo,
            new SynchronousMessageDispatcher($eventConsumer),
            2
        );

        $projectionEngine->processNewEvents();

        $this->assertCount(10, $eventConsumer->getHandledMessages());
        $this->assertTrue($projectionEngineInMemoryRepo->lockWasObtained());
        $this->assertFalse($projectionEngineInMemoryRepo->isLocked());

        $this->assertEquals(10, $projectionEngineInMemoryRepo->getOffset());
    }

    /** @test */
    public function it_starts_from_the_previous_stored_offset(): void
    {
        $messageRepository = new InMemoryReplayMessageRepository();
        $messageRepository->persist(
            ...array_map(
            fn ($number) => new Message(new EventStub((string) $number)),
            range(1, 10)
        )
        );

        $projectionEngineInMemoryRepo = new InMemoryProjectionEngineStateRepository('test-consumer');
        $projectionEngineInMemoryRepo->storeOffset(5);

        $eventConsumer = new EventConsumerStub();

        $projectionEngine = new ProjectionEngine(
            $messageRepository,
            $projectionEngineInMemoryRepo,
            $projectionEngineInMemoryRepo,
            new SynchronousMessageDispatcher($eventConsumer),
            10
        );

        $projectionEngine->processNewEvents();

        $this->assertCount(5, $eventConsumer->getHandledMessages());
        $this->assertEquals('6', $eventConsumer->getHandledMessages()[0]->event()->value);
        $this->assertEquals(10, $projectionEngineInMemoryRepo->getOffset());
    }

    /** @test */
    public function it_resets_the_offset_on_replay(): void
    {
        $messageRepository = new InMemoryReplayMessageRepository();
        $messageRepository->persist(
            ...array_map(
                fn ($number) => new Message(new EventStub((string) $number)),
                range(1, 10)
            )
        );

        $projectionEngineInMemoryRepo = new InMemoryProjectionEngineStateRepository('test-consumer');
        $projectionEngineInMemoryRepo->storeOffset(5);

        $eventConsumer = new EventConsumerStub();

        $projectionEngine = new ProjectionEngine(
            $messageRepository,
            $projectionEngineInMemoryRepo,
            $projectionEngineInMemoryRepo,
            new SynchronousMessageDispatcher($eventConsumer),
            10
        );

        $projectionEngine->startReplay();

        $this->assertCount(10, $eventConsumer->getHandledMessages());
        $this->assertEquals('1', $eventConsumer->getHandledMessages()[0]->event()->value);
        $this->assertEquals(10, $projectionEngineInMemoryRepo->getOffset());
    }

    /** @test */
    public function when_the_consumer_implements_the__resets_state_before_replay_interface_it_gets_called_to_reset_state_on_replay(): void
    {
        $messageRepository = new InMemoryReplayMessageRepository();
        $projectionEngineInMemoryRepo = new InMemoryProjectionEngineStateRepository('test-consumer');
        $dispatcher = new MessageDispatcherThatResetsState(new EventConsumerStub());

        $projectionEngine = new ProjectionEngine(
            $messageRepository,
            $projectionEngineInMemoryRepo,
            $projectionEngineInMemoryRepo,
            $dispatcher,
            10
        );

        $projectionEngine->startReplay();

        $this->assertTrue($dispatcher->wasReset());
    }
}
