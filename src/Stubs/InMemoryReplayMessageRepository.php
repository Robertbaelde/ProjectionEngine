<?php

declare(strict_types=1);

namespace Robertbaelde\ProjectionEngine\Stubs;

use EventSauce\EventSourcing\Message;
use Generator;
use Robertbaelde\ProjectionEngine\ReplayMessageRepository;

class InMemoryReplayMessageRepository implements ReplayMessageRepository
{
    /**
     * @var Message[]
     */
    private array $messages = [];

    public function persist(Message ...$messages): void
    {
        foreach ($messages as $message) {
            $this->messages[] = $message;
        }
    }

    public function retrieveForReplayFromOffset(int $offset = 0, int $pageSize = 1000): Generator
    {
        $messageCount = 0;

        foreach (array_slice($this->messages, $offset, $pageSize) as $message) {
            ++$messageCount;
            yield $message;
        }

        return $offset + $messageCount;
    }

    public function hasMessagesAfterOffset(int $offset): bool
    {
        return count(array_slice($this->messages, $offset, 1)) > 0;
    }
}
