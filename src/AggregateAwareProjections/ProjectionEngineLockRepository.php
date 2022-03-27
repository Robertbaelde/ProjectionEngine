<?php

declare(strict_types=1);

namespace Robertbaelde\ProjectionEngine\AggregateAwareProjections;

interface ProjectionEngineLockRepository
{
    /**
     * @throws ConsumerIsLockedByOtherProcess
     */
    public function lockForHandlingMessages(string $aggregateRootId): void;

    public function releaseLock(string $aggregateRootId): void;
}
