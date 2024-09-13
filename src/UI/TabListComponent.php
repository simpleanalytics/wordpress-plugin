<?php

namespace SimpleAnalytics\UI;

use SimpleAnalytics\Settings\Tab;

readonly class TabListComponent
{
    public function __construct(
        private string $pageSlug,
        private Tab    $currentTab,
        /** @var Tab[] */
        private array  $tabs = [],
    ) {
    }

    public function __invoke(): void
    {
        ?>
        <nav class="-mb-px flex gap-5">
            <?php foreach ($this->tabs as $tab): ?>
                <a
                    href="<?php echo $this->tabUrl($tab); ?>"
                    class="<?php echo $this->tabClass($tab); ?>"
                >
                    <?php echo $this->tabIcon($tab); ?>
                    <?php echo $tab->getName(); ?>
                </a>
            <?php endforeach ?>
        </nav>
        <?php
    }

    protected function tabUrl(Tab $tab): string
    {
        $url = admin_url('options-general.php');

        return add_query_arg(['page' => $this->pageSlug, 'tab' => $tab->getSlug()], $url);
    }

    protected function tabClass(Tab $tab): string
    {
        return implode(' ', [
            'pb-2 border-b-3 px-3.5',
            'whitespace-nowrap text-sm font-medium',
            $this->currentTab->getSlug() === $tab->getSlug()
                ? 'border-primary text-primary'
                : 'text-littleMuted border-transparent hover:border-gray-300 hover:text-gray-700'
        ]);
    }

    protected function tabIcon(Tab $tab): ?string
    {
        if ($icon = $tab->getIcon()) {
            return $icon->class('mr-1 inline-block h-4 w-4');
        }

        return null;
    }
}
