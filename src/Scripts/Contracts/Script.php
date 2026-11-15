<?php

namespace SimpleAnalytics\Scripts\Contracts;

defined('ABSPATH') || exit;

interface Script
{
    public function path(): string;

    function handle(): string;
}
