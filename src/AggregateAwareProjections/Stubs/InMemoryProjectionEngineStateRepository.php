<?php

declare(strict_types=1);

namespace Robertbaelde\ProjectionEngine\AggregateAwareProjections\Stubs;

use Robertbaelde\ProjectionEngine\AggregateAwareProjections\ConsumerIsLockedByOtherProcess;
use Robertbaelde\ProjectionEngine\AggregateAwareProjections\ProjectionEngineLockRepository;
use Robertbaelde\ProjectionEngine\AggregateAwareProjections\ProjectionEngineStateRepository;

class InMemoryProjectionEngineStateRepository implements ProjectionEngineStateRepository, ProjectionEngineLockRepository
{
    private array $aggregates = [];
    private array $consumerLocks = [];
    private bool $lockWasObtained = false;

    public function __construct(string $consumerId)
    {
        $this->consumerId = $consumerId;
    }

    public function storeOffset(string $aggregateId, int $offset): void
    {
        $this->aggregates[$aggregateId] = $offset;
    }

    public function getProjectorOffsetForAggregate(string $aggregateId): int
    {
        return $this->aggregates[$aggregateId] ?? 0;
    }

    /**
     * @throws ConsumerIsLockedByOtherProcess
     */
    public function lockForHandlingMessages(string $aggregateRootId): void
    {
        if ($this->isLocked($aggregateRootId)) {
            throw new ConsumerIsLockedByOtherProcess();
        }
        $this->lockWasObtained = true;
        $this->consumerLocks[$aggregateRootId] = true;
    }

    public function releaseLock(string $aggregateRootId): void
    {
        $this->consumerLocks[$aggregateRootId] = false;
    }

    public function isLocked(string $aggregateRootId): bool
    {
        return $this->consumerLocks[$aggregateRootId] ?? false;
    }

    public function lockWasObtained(): bool
    {
        return $this->lockWasObtained;
    }
}
