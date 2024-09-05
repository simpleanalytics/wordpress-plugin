<?php

namespace SimpleAnalytics\UI;

use SimpleAnalytics\Settings\{Page, Tab};
use const SimpleAnalytics\PLUGIN_URL;

readonly class PageComponent
{
    public function __construct(private Page $page)
    {
    }

    public function __invoke(): void
    {
        $tabs = $this->page->getTabs();
        $currentTab = $this->getCurrentTab($tabs);
        $optionGroup = $this->page->getSlug() . '-' . $currentTab->getSlug();
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
            <link rel="stylesheet" href="<?php echo PLUGIN_URL ?>assets/css/settings.css">
            <form method="post" action="options.php">
                <!-- Hidden fields -->
                <?php settings_fields($optionGroup) ?>

                <!-- Header / Nav -->
                <header class="pt-5 bg-primaryBg">
                    <div class="mx-auto max-w-3xl flex-col justify-between gap-5 sm:flex sm:items-baseline">
                        <!-- Logo -->
                        <a
                            href="https://dashboard.simpleanalytics.com/websites"
                            target="_blank"
                            class="text-base font-semibold leading-6 text-gray-900"
                        >
                            <img
                                alt="SimpleAnalytics logo"
                                src="<?php echo PLUGIN_URL ?>assets/logo.svg"
                                class="mr-2 inline-block h-10 w-auto text-primary"
                            >
                        </a>

                        <!-- Tabs -->
                        <div class="mt-4 sm:mt-0">
                            <?php (new TabListComponent($this->page->getSlug(), $currentTab, $tabs))() ?>
                        </div>
                    </div>
                </header>

                <!-- Fields / Layout -->
                <div class="mx-auto max-w-3xl bg-white px-4 py-6 sm:px-4 lg:px-0">
                    <div class="border-b border-gray-900/10 pb-7">
                        <?php $currentTab->render() ?>
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
        foreach ($tabs as $tab) if ($tab->getSlug() === $slug) {
            return $tab;
        }

        return null;
    }
}
