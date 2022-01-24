<?php

namespace Robertbaelde\ProjectionEngine\Stubs;

use EventSauce\EventSourcing\Message;
use EventSauce\EventSourcing\MessageDispatcher;
use Robertbaelde\ProjectionEngine\ResetsStateBeforeReplay;

class MessageDispatcherThatResetsState implements MessageDispatcher, ResetsStateBeforeReplay
{

    private bool $reset = false;

    public function dispatch(Message ...$messages): void
    {

    }

    public function resetBeforeReplay(): void
    {
        $this->reset = true;
    }

    public function wasReset(): bool
    {
        return $this->reset;
    }
}
