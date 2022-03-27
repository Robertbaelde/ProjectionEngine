<?php

namespace Robertbaelde\ProjectionEngine\AggregateAwareProjections;

use EventSauce\EventSourcing\AggregateRootId;

interface ProjectionEngineInterface
{
    /**
     * @throws ConsumerIsLockedByOtherProcess
     */
    public function processMessageForAggregate(AggregateRootId $aggregateRootId): void;

    /**
     * @throws ConsumerIsLockedByOtherProcess
     */
    public function startReplayForAggregate(AggregateRootId $aggregateRootId): void;
}
