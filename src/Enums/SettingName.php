<?php

namespace SimpleAnalytics\Enums;

defined('\\ABSPATH') || exit;

/**
 * We use this class to avoid using magic strings in our code.
 */
class SettingName
{
    const string CUSTOM_DOMAIN = 'simpleanalytics_custom_domain';
    const string EXCLUDED_IP_ADDRESSES = 'simpleanalytics_excluded_ip_addresses';
    const string EXCLUDED_ROLES = 'simpleanalytics_exclude_user_roles';
    const string HASH_MODE = 'simpleanalytics_hash_mode';
    const string COLLECT_DNT = 'simpleanalytics_collect_dnt';
    const string IGNORE_PAGES = 'simpleanalytics_ignore_pages';
    const string MANUAL_COLLECT = 'simpleanalytics_manual_collect';
    const string ONLOAD_CALLBACK = 'simpleanalytics_onload_callback';
    const string HOSTNAME = 'simpleanalytics_hostname';
    const string SA_GLOBAL = 'simpleanalytics_sa_global';
    const string ENABLED = 'simpleanalytics_enabled';
    const string AUTOMATED_EVENTS = 'simpleanalytics_automated_events';
    const string EVENT_COLLECT_DOWNLOADS = 'simpleanalytics_event_collect_downloads';
    const string EVENT_EXTENSIONS = 'simpleanalytics_event_extensions';
    const string EVENT_USE_TITLE = 'simpleanalytics_event_use_title';
    const string EVENT_FULL_URLS = 'simpleanalytics_event_full_urls';
    const string EVENT_SA_GLOBAL = 'simpleanalytics_event_sa_global';
}
