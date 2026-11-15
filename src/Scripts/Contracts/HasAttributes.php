<?php

namespace SimpleAnalytics\Scripts\Contracts;

defined('ABSPATH') || exit;

interface HasAttributes
{
    public function attributes(): array;
}
