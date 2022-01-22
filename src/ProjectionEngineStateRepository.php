<?php

namespace Robertbaelde\ProjectionEngine;

interface ProjectionEngineStateRepository
{
    public function __construct(string $consumerId);

    public function storeOffset(int $offset): void;

    public function getOffset(): ?int;
}
