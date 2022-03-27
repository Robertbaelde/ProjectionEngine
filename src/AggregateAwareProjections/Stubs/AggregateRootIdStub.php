<?php

namespace Robertbaelde\ProjectionEngine\AggregateAwareProjections\Stubs;

use EventSauce\EventSourcing\AggregateRootId;

class AggregateRootIdStub implements AggregateRootId
{
    public function __construct(public string $id)
    {
    }

    public function toString(): string
    {
        return $this->id;
    }

    public static function fromString(string $aggregateRootId): AggregateRootId
    {
        return new self($aggregateRootId);
    }
}
