<?php

declare(strict_types=1);

namespace Robertbaelde\ProjectionEngine\AggregateAwareProjections;

use EventSauce\EventSourcing\Message;
use EventSauce\EventSourcing\MessageRepository;
use EventSauce\EventSourcing\UnableToRetrieveMessages;
use Generator;

interface ReplayMessageRepository extends MessageRepository
{
//    public function hasMessagesAfterOffset(int $offset): bool;
}
