<?php

namespace SimpleAnalytics\Enums;

defined('\\ABSPATH') || exit;

class Setting
{
    const CUSTOM_DOMAIN = 'simpleanalytics_custom_domain';
    const EXCLUDED_IP_ADDRESSES = 'simpleanalytics_excluded_ip_addresses';
    const EXCLUDED_ROLES = 'simpleanalytics_exclude_user_roles';
    const MODE = 'simpleanalytics_mode';
    const COLLECT_DNT = 'simpleanalytics_collect_dnt';
    const IGNORE_PAGES = 'simpleanalytics_ignore_pages';
    const AUTO_COLLECT = 'simpleanalytics_auto_collect';
    const ONLOAD_CALLBACK = 'simpleanalytics_onload_callback';
    const SA_GLOBAL = 'simpleanalytics_sa_global';
    const ENABLED = 'simpleanalytics_enabled';
    const EVENT_COLLECT = 'simpleanalytics_event_collect';
    const EVENT_EXTENSIONS = 'simpleanalytics_event_extensions';
    const EVENT_USE_TITLE = 'simpleanalytics_event_use_title';
    const EVENT_FULL_URLS = 'simpleanalytics_event_full_urls';
    const EVENT_SA_GLOBAL = 'simpleanalytics_event_sa_global';
}
