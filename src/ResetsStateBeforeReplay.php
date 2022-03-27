<?php

declare(strict_types=1);

namespace Robertbaelde\ProjectionEngine;

interface ResetsStateBeforeReplay
{
    public function resetBeforeReplay(): void;
}
