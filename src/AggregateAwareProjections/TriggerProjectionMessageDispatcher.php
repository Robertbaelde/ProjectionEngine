<?php

namespace Robertbaelde\ProjectionEngine\AggregateAwareProjections;

use EventSauce\EventSourcing\AggregateRootId;
use EventSauce\EventSourcing\Message;
use EventSauce\EventSourcing\MessageDispatcher;

class TriggerProjectionMessageDispatcher implements MessageDispatcher
{
    public function __construct(protected ProjectionEngineInterface $projectionEngine)
    {
    }

    public function dispatch(Message ...$messages): void
    {
        $aggregateRootId = $this->getAggregateRootId(...$messages);
        if($aggregateRootId === null){
            return;
        }
        $this->projectionEngine->processMessageForAggregate($aggregateRootId);
    }

    private function getAggregateRootId(Message ...$messages): ?AggregateRootId
    {
        foreach ($messages as $message){
            if($message->aggregateRootId() !== null)
            {
                return $message->aggregateRootId();
            }
        }
        return null;
    }


}
