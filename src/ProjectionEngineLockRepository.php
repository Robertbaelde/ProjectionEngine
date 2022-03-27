<?php

declare(strict_types=1);

namespace Robertbaelde\ProjectionEngine;

interface ProjectionEngineLockRepository
{
    public function __construct(string $consumerId);

    /**
     * @throws ConsumerIsLockedByOtherProcess
     */
    public function lockForHandlingMessages(string $aggregateRootId): void;

    public function releaseLock(string $aggregateRootId): void;
}
