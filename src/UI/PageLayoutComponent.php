<?php

namespace SimpleAnalytics\UI;

use SimpleAnalytics\Settings\{AdminPage, Tab};
use function SimpleAnalytics\get_icon;
use const SimpleAnalytics\PLUGIN_URL;

class PageLayoutComponent
{
    /**
     * @readonly
     * @var \SimpleAnalytics\Settings\AdminPage
     */
    private $page;
    public function __construct(AdminPage $page)
    {
        $this->page = $page;
    }

    public function __invoke(): void
    {
        $currentTab = $this->getCurrentTab($this->page->getTabs());
        ?>
        <style>
            #wpwrap {
                background: white;
            }

            #wpcontent {
                padding-left: 0;
            }
        </style>
        <template shadowrootmode="open">
            <link rel="preconnect" href="https://fonts.bunny.net">
            <link rel="stylesheet" href="<?php echo PLUGIN_URL ?>build/css/settings.css">
            <form method="post" action="options.php">
                <!-- Hidden fields -->
                <?php settings_fields($this->page->getOptionGroup($currentTab)); ?>

                <!-- Header / Nav -->
                <header class="pt-5 bg-primaryBg">
                    <div class="mx-auto max-w-3xl flex-col justify-between gap-5 sm:flex sm:items-baseline">
                        <div class="flex items-center">
                            <!-- Logo -->
                            <a
                                href="https://dashboard.simpleanalytics.com/websites"
                                target="_blank"
                                class="text-base font-semibold leading-6 text-gray-900"
                            >
                                <img
                                    alt="SimpleAnalytics logo"
                                    src="<?php echo PLUGIN_URL ?>logo.svg"
                                    class="mr-2 inline-block h-10 w-auto text-primary"
                                >
                            </a>
                            <!-- "Open Dashboard" link -->
                            <a
                                href="https://dashboard.simpleanalytics.com/websites"
                                target="_blank"
                                class="inline-flex items-center rounded bg-white px-2 py-1 text-xs font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
                            >
                                <?php echo get_icon('external')->class('w-3.5 h-3.5 mr-0.5'); ?>
                                Open Dashboard
                            </a>
                        </div>

                        <!-- Tabs -->
                        <div class="mt-4 sm:mt-0">
                            <?php (new TabListComponent($this->page->getSlug(), $currentTab, $this->page->getTabs()))(); ?>
                        </div>
                    </div>
                </header>

                <!-- Fields / Layout -->
                <div class="mx-auto max-w-3xl bg-white px-4 py-6 sm:px-4 lg:px-0">
                    <div class="border-b border-gray-900/10 pb-7">
                        <?php $currentTab->render(); ?>
                    </div>

                    <div class="mt-6 flex items-center justify-start gap-x-6">
                        <button
                            type="submit"
                            class="rounded-md px-3 py-2 text-sm font-semibold text-white shadow-sm bg-primary hover:bg-red-500 focus-visible:outline-primary focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2"
                        >
                            Save Changes
                        </button>
                    </div>
                </div>
            </form>
            <script>
                /**
                 * Add value as a new line to textarea
                 *
                 * @param textarea HTMLTextAreaElement
                 * @param value string
                 */
                function sa_textarea_add_value(textarea, value) {
                    if (textarea.value.includes(value)) {
                        return;
                    }

                    if (textarea.value.trim() === "") {
                        textarea.value = value;
                    } else {
                        textarea.value += `\n${value}`;
                    }
                }
            </script>
        </template>
        <script>
            // Polyfill in case the browser has no support for shadowRootMode
            // 1. https://developer.chrome.com/docs/css-ui/declarative-shadow-dom#polyfill
            // 2. https://caniuse.com/mdn-html_elements_template_shadowrootmode
            (function attachShadowRoots(root) {
                root.querySelectorAll("template[shadowrootmode]").forEach(template => {
                    const mode = template.getAttribute("shadowrootmode");
                    const shadowRoot = template.parentNode.attachShadow({ mode });
                    shadowRoot.appendChild(template.content);
                    template.remove();
                    attachShadowRoots(shadowRoot);
                });
            })(document);
        </script>
        <?php
    }

    protected function getCurrentTab(array $tabs): Tab
    {
        $currentTabSlug = $_GET['tab'] ?? $tabs[0]->getSlug();

        return $this->findTabBySlug($tabs, $currentTabSlug) ?? $tabs[0];
    }

    protected function findTabBySlug(array $tabs, string $slug): ?Tab
    {
        foreach ($tabs as $tab) {
            if ($tab->getSlug() === $slug) return $tab;
        }

        return null;
    }
}
