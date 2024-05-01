<?php

namespace SimpleAnalytics\Scripts;

abstract class Script
{
    abstract public function getPath(): string;

    abstract function getHandle(): string;

    public function getAttributes(): array
    {
        return [];
    }
}
