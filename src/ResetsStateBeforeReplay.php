<?php

declare(strict_types=1);

namespace Robertbaelde\ProjectionEngine;

use EventSauce\EventSourcing\AggregateRootId;

interface ResetsStateBeforeReplay
{
    public function resetBeforeReplay(AggregateRootId $aggregateRootId): void;
}
