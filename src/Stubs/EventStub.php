<?php

namespace Robertbaelde\ProjectionEngine\Stubs;

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
