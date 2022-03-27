<?php

namespace Robertbaelde\ProjectionEngine;

use EventSauce\BackOff\BackOffStrategy;
use EventSauce\BackOff\ExponentialBackOffStrategy;
use Throwable;

class WaitingForLockRepository implements ProjectionEngineLockRepository
{
    private int $tries = 0;
    private BackOffStrategy $backOff;

    public function __construct(
        protected ProjectionEngineLockRepository $lockRepository,
        BackOffStrategy $backOff = null,
    )
    {
        $this->backOff = $backOff ?: new ExponentialBackOffStrategy(10000, 25);
    }

    public function lockForHandlingMessages(string $aggregateRootId): void
    {
        startOfRequiringLock:
        try {
            $this->tries++;
            $this->lockRepository->lockForHandlingMessages($aggregateRootId);
        } catch (Throwable $throwable)
        {
            $this->backOff->backOff($this->tries, $throwable);
            goto startOfRequiringLock;
        }
    }

    public function releaseLock(string $aggregateRootId): void
    {
        $this->lockRepository->releaseLock($aggregateRootId);
    }
}
