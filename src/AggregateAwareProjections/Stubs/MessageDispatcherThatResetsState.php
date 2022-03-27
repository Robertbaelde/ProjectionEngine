<?php

declare(strict_types=1);

namespace Robertbaelde\ProjectionEngine\AggregateAwareProjections\Stubs;

use EventSauce\EventSourcing\AggregateRootId;
use EventSauce\EventSourcing\Message;
use EventSauce\EventSourcing\MessageDispatcher;
use Robertbaelde\ProjectionEngine\AggregateAwareProjections\ResetsStateBeforeReplay;

class MessageDispatcherThatResetsState implements MessageDispatcher, ResetsStateBeforeReplay
{
    private bool $reset = false;

    public function dispatch(Message ...$messages): void
    {
    }

    public function resetBeforeReplay(AggregateRootId $aggregateRootId): void
    {
        $this->reset = true;
    }

    public function wasReset(): bool
    {
        return $this->reset;
    }
}
