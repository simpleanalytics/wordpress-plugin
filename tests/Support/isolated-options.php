<?php

/** Run only through wp eval-file against the local wp-env installation. */
function sa_assert($condition, string $message): void
{
    if (! $condition) throw new RuntimeException($message);
}

function sa_with_isolated_options(callable $test): void
{
    global $wpdb, $wp_object_cache;

    sa_assert(! wp_using_ext_object_cache(), 'Regression tests require the default object cache.');
    $originalTable = $wpdb->options;
    $originalCache = $wp_object_cache;
    $table = 'sa_regression_options';
    $guard = static function ($sql) use ($table) {
        if (! preg_match('/^\s*(SELECT|SHOW|DESCRIBE|EXPLAIN)\b/i', $sql) && strpos($sql, $table) === false) {
            throw new RuntimeException('Refusing a write outside the temporary test table.');
        }
        return $sql;
    };
    add_filter('query', $guard, 9999);

    try {
        sa_assert($wpdb->query("CREATE TEMPORARY TABLE $table LIKE $originalTable") !== false, $wpdb->last_error);
        sa_assert($wpdb->query("INSERT INTO $table SELECT * FROM $originalTable") !== false, $wpdb->last_error);
        $wpdb->options = $table;
        $wp_object_cache = new WP_Object_Cache();
        wp_cache_switch_to_blog(get_current_blog_id());
        $test();
        echo "Regression checks passed\n";
    } finally {
        $wpdb->options = $originalTable;
        $wp_object_cache = $originalCache;
        remove_filter('query', $guard, 9999);
    }
    // The database connection automatically drops the temporary table on exit.
}
