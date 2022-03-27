<?php

namespace Robertbaelde\ProjectionEngine;

class WaitingForLockRepository implements ProjectionEngineLockRepository
{
    private int $tries = 0;

    public function __construct(
        protected ProjectionEngineLockRepository $lockRepository,
        protected int $sleepInMicroseconds = 1000,
        protected int $maxAmountOfTries = 2
    )
    {
    }

    public function lockForHandlingMessages(string $aggregateRootId): void
    {
        startOfRequiringLock:
        try {
            $this->lockRepository->lockForHandlingMessages($aggregateRootId);
        } catch (ConsumerIsLockedByOtherProcess $locked)
        {
            $this->tries++;
            if($this->tries > $this->maxAmountOfTries){
                throw new ConsumerIsLockedByOtherProcess();
            }
            usleep($this->sleepInMicroseconds);
            goto startOfRequiringLock;
        }
    }

    public function releaseLock(string $aggregateRootId): void
    {
        $this->lockRepository->releaseLock($aggregateRootId);
    }
}
