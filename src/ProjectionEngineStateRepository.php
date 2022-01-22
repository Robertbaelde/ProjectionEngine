<?php

namespace Robertbaelde\ProjectionEngine;

interface ProjectionEngineStateRepository
{
    public function __construct(string $consumerId);

    public function storeOffset(string $offset): void;

    public function getOffset(): ?string;

}
