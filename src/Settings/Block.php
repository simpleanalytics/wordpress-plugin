<?php

namespace SimpleAnalytics\Settings;

defined('ABSPATH') || exit;

interface Block
{
    public function render(): void;
}
