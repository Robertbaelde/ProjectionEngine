<?php

namespace Robertbaelde\ProjectionEngine;

use EventSauce\EventSourcing\MessageDispatcher;

class ProjectionEngine
{
    public function __construct(
        protected ReplayMessageRepository $messages,
        protected ProjectionEngineStateRepository $state,
        protected ProjectionEngineLockRepository $lock,
        protected MessageDispatcher $dispatcher,
        protected int $pageSize = 100
    ) {
    }

    /**
     * @throws ConsumerIsLockedByOtherProcess
     */
    public function processNewEvents(): void
    {
        // claim lock
        $this->lock->lockForHandlingMessages();

        $offset = $this->state->getOffset() ?? 0;

        while ($this->messages->hasMessagesAfterOffset($offset)) {
            $messages = $this->messages->retrieveForReplayFromOffset($offset, $this->pageSize);

            $this->dispatcher->dispatch(...$messages);

            $offset = $messages->getReturn();
            $this->state->storeOffset($offset);
        }

        $this->lock->releaseLock();
    }
}
