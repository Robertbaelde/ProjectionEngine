<?php

declare(strict_types=1);

namespace Robertbaelde\ProjectionEngine\AggregateAwareProjections;

interface ProjectionEngineStateRepository
{
    public function __construct(string $consumerId);

    public function storeOffset(string $aggregateId, int $offset): void;

    public function getProjectorOffsetForAggregate(string $aggregateId): int;
}
