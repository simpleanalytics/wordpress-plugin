<?php

defined('ABSPATH') || exit;

require __DIR__ . '/../Support/isolated-options.php';

sa_with_isolated_options(static function () {
    $page = new SimpleAnalytics\Settings\AdminPage('Test');
    $layout = new class($page) extends SimpleAnalytics\UI\PageLayoutComponent {
        public function current(array $tabs): SimpleAnalytics\Settings\Tab { return $this->getCurrentTab($tabs); }
    };
    $general = new SimpleAnalytics\Settings\Tab('General', 'general');
    $advanced = new SimpleAnalytics\Settings\Tab('<script>alert(1)</script>', 'advanced');
    $tabs = [$general, $advanced];
    $_GET['tab'] = ['x'];
    sa_assert($layout->current($tabs) === $general, 'An array tab must fall back to General.');
    $_GET['tab'] = 'missing';
    sa_assert($layout->current($tabs) === $general, 'An unknown tab must fall back to General.');
    $_GET['tab'] = 'advanced';
    sa_assert($layout->current($tabs) === $advanced, 'Valid tabs must still work.');
    unset($_GET['tab']);

    ob_start();
    (new SimpleAnalytics\UI\TabListComponent('test', $general, $tabs))();
    $html = ob_get_clean();
    sa_assert(strpos($html, '<script>') === false, 'Tab names must be escaped.');
    sa_assert(strpos($html, '&lt;script&gt;') !== false, 'Escaped tab text must remain visible.');
    sa_assert(strpos($html, '&#038;tab=') !== false, 'Tab URLs must be escaped.');
});
