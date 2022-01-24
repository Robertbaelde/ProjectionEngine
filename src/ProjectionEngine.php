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

        $this->processMessages();

        $this->lock->releaseLock();
    }

    /**
     * @throws ConsumerIsLockedByOtherProcess
     */
    public function startReplay(): void
    {
        $this->lock->lockForHandlingMessages();

        if($this->dispatcher instanceof ResetsStateBeforeReplay)
        {
            $this->dispatcher->resetBeforeReplay();
        }

        $this->state->storeOffset(0);

        $this->processMessages();

        $this->lock->releaseLock();
    }

    private function processMessages(): void
    {
        $offset = $this->state->getOffset() ?? 0;

        while ($this->messages->hasMessagesAfterOffset($offset)) {
            $messages = $this->messages->retrieveForReplayFromOffset($offset, $this->pageSize);

            $this->dispatcher->dispatch(...$messages);

            $offset = $messages->getReturn();
            if (!is_int($offset)) {
                throw new \LogicException("Generator must return offset as a int in the return");
            }
            $this->state->storeOffset($offset);
        }
    }
}
