<?php

declare(strict_types=1);

namespace Robertbaelde\ProjectionEngine;

use EventSauce\EventSourcing\AggregateRootId;
use EventSauce\EventSourcing\Message;
use EventSauce\EventSourcing\MessageDispatcher;

class ProjectionEngine implements ProjectionEngineInterface
{
    public function __construct(
        protected ReplayMessageRepository $messages,
        protected ProjectionEngineStateRepository $state,
        protected ProjectionEngineLockRepository $lock,
        protected MessageDispatcher $dispatcher
    ) {
    }

    /**
     * @throws ConsumerIsLockedByOtherProcess
     */
    public function processMessageForAggregate(AggregateRootId $aggregateRootId): void
    {
        // claim lock
        $this->lock->lockForHandlingMessages($aggregateRootId->toString());

        $currentProjectionVersion = $this->state->getProjectorOffsetForAggregate($aggregateRootId->toString());
        $messages = $this->messages->retrieveAllAfterVersion($aggregateRootId, $currentProjectionVersion);
        $messages = iterator_to_array($messages);

        if(count($messages) !== 0){
            $this->passMessageToConsumer(...$messages);
            $this->state->storeOffset($aggregateRootId->toString(), end($messages)->aggregateVersion());
        }

        $this->lock->releaseLock($aggregateRootId->toString());
    }

    /**
     * @throws ConsumerIsLockedByOtherProcess
     */
    public function startReplayForAggregate(AggregateRootId $aggregateRootId): void
    {
        $this->lock->lockForHandlingMessages($aggregateRootId->toString());

        if ($this->dispatcher instanceof ResetsStateBeforeReplay) {
            $this->dispatcher->resetBeforeReplay($aggregateRootId);
        }

        $this->state->storeOffset($aggregateRootId->toString(), 0);
        $this->lock->releaseLock($aggregateRootId->toString());

        $this->processMessageForAggregate($aggregateRootId);
    }


    private function passMessageToConsumer(Message ...$messages) : void
    {
        $this->dispatcher->dispatch(...$messages);
    }

    private function makeSureMessagesAreInOrder()
    {
        // todo
    }
}
