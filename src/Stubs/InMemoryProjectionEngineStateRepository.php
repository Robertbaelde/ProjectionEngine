<?php

namespace Robertbaelde\ProjectionEngine\Stubs;

use Robertbaelde\ProjectionEngine\ConsumerIsLockedByOtherProcess;
use Robertbaelde\ProjectionEngine\ProjectionEngineLockRepository;
use Robertbaelde\ProjectionEngine\ProjectionEngineStateRepository;

class InMemoryProjectionEngineStateRepository implements ProjectionEngineStateRepository, ProjectionEngineLockRepository
{
    private string $consumerId;
    private array $consumers = [];
    private array $consumerLocks = [];
    private bool $lockWasObtained = false;

    public function __construct(string $consumerId)
    {
        $this->consumerId = $consumerId;
    }

    public function storeOffset(int $offset): void
    {
        $this->consumers[$this->consumerId] = $offset;
        $this->consumerLocks[$this->consumerId] = false;
    }

    public function getOffset(): ?int
    {
        return $this->consumers[$this->consumerId] ?? null;
    }

    /**
     * @throws ConsumerIsLockedByOtherProcess
     */
    public function lockForHandlingMessages(): void
    {
        if ($this->isLocked()) {
            throw new ConsumerIsLockedByOtherProcess();
        }
        $this->lockWasObtained = true;
        $this->consumerLocks[$this->consumerId] = true;
    }

    public function releaseLock(): void
    {
        $this->consumerLocks[$this->consumerId] = false;
    }

    public function isLocked(): bool
    {
        return $this->consumerLocks[$this->consumerId] ?? false;
    }

    public function lockWasObtained(): bool
    {
        return $this->lockWasObtained;
    }
}
