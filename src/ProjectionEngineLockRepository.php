<?php

declare(strict_types=1);

namespace Robertbaelde\ProjectionEngine;

interface ProjectionEngineLockRepository
{
    public function __construct(string $consumerId);

    /**
     * @throws ConsumerIsLockedByOtherProcess
     */
    public function lockForHandlingMessages(): void;

    public function releaseLock(): void;
}
