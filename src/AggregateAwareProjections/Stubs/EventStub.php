<?php

declare(strict_types=1);

namespace Robertbaelde\ProjectionEngine\AggregateAwareProjections\Stubs;

use EventSauce\EventSourcing\Serialization\SerializablePayload;

class EventStub implements SerializablePayload
{
    public function __construct(public readonly string $value)
    {
    }

    public function toPayload(): array
    {
        return ['value' => $this->value];
    }

    public static function fromPayload(array $payload): SerializablePayload
    {
        return new self($payload['value']);
    }
}
