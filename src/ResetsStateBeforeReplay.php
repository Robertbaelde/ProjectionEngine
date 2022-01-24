<?php

namespace Robertbaelde\ProjectionEngine;

interface ResetsStateBeforeReplay
{
    public function resetBeforeReplay(): void;
}
