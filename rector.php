<?php

declare(strict_types=1);

use Rector\Set\ValueObject\DowngradeLevelSetList;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/simple-analytics.php',
        __DIR__ . '/helpers.php'
    ])
    ->withSets([
        // https://make.wordpress.org/core/handbook/references/php-compatibility-and-wordpress-versions/
        DowngradeLevelSetList::DOWN_TO_PHP_72
    ]);
