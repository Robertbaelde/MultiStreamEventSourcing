<?php

namespace EventStream;

class OptimisticLock
{
    /**
     * @var LastExpectedInStream[]
     */
    public readonly array $lastExpectedInStream;

    public function __construct(
        LastExpectedInStream ...$lastExpectedInStream
    )
    {
        $this->lastExpectedInStream = $lastExpectedInStream;
    }
}
