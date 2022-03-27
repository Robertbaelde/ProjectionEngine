<?php

declare(strict_types=1);

namespace Robertbaelde\ProjectionEngine\AggregateAwareProjections\Stubs;

use EventSauce\EventSourcing\Message;
use EventSauce\EventSourcing\MessageConsumer;

class EventConsumerStub implements MessageConsumer
{
    private array $handledMessages = [];

    public function __construct()
    {
    }

    public function handle(Message $message): void
    {
        $this->handledMessages[] = $message;
    }

    public function getHandledMessages(): array
    {
        return $this->handledMessages;
    }
}
