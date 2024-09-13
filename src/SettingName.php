<?php

namespace SimpleAnalytics;

defined('\\ABSPATH') || exit;

/**
 * We use these values to avoid using magic strings in our code.
 */
class SettingName
{
    /**
     * @var string
     */
    const CUSTOM_DOMAIN = 'simpleanalytics_custom_domain';
    /**
     * @var string
     */
    const NOSCRIPT = 'simpleanalytics_noscript';
    /**
     * @var string
     */
    const EXCLUDED_IP_ADDRESSES = 'simpleanalytics_excluded_ip_addresses';
    /**
     * @var string
     */
    const EXCLUDED_ROLES = 'simpleanalytics_exclude_user_roles';
    /**
     * @var string
     */
    const HASH_MODE = 'simpleanalytics_hash_mode';
    /**
     * @var string
     */
    const COLLECT_DNT = 'simpleanalytics_collect_dnt';
    /**
     * @var string
     */
    const IGNORE_PAGES = 'simpleanalytics_ignore_pages';
    /**
     * @var string
     */
    const MANUAL_COLLECT = 'simpleanalytics_manual_collect';
    /**
     * @var string
     */
    const ONLOAD_CALLBACK = 'simpleanalytics_onload_callback';
    /**
     * @var string
     */
    const HOSTNAME = 'simpleanalytics_hostname';
    /**
     * @var string
     */
    const SA_GLOBAL = 'simpleanalytics_sa_global';
    /**
     * @var string
     */
    const ENABLED = 'simpleanalytics_enabled';
    /**
     * @var string
     */
    const AUTOMATED_EVENTS = 'simpleanalytics_automated_events';
    /**
     * @var string
     */
    const EVENT_COLLECT_DOWNLOADS = 'simpleanalytics_event_collect_downloads';
    /**
     * @var string
     */
    const EVENT_EXTENSIONS = 'simpleanalytics_event_extensions';
    /**
     * @var string
     */
    const EVENT_USE_TITLE = 'simpleanalytics_event_use_title';
    /**
     * @var string
     */
    const EVENT_FULL_URLS = 'simpleanalytics_event_full_urls';
    /**
     * @var string
     */
    const EVENT_SA_GLOBAL = 'simpleanalytics_event_sa_global';

    static function cases(): array
    {
        return (new \ReflectionClass(__CLASS__))->getConstants();
    }
}
