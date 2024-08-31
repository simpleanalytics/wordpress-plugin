<?php

namespace SimpleAnalytics\Scripts;

interface Script
{
    public function path(): string;

    function handle(): string;
}
