<?php

namespace SimpleAnalytics\UI;

use SimpleAnalytics\Settings\Tab;

readonly class TabListComponent
{
    public function __construct(
        protected string $pageSlug,
        protected Tab    $currentTab,
        protected array  $tabs = [],
    ) {
    }

    public function render(): void
    {
        ?>
        <nav class="-mb-px flex gap-5">
            <?php foreach ($this->tabs as $tab): ?>
                <a
                    href="<?php echo add_query_arg([
                        'page' => $this->pageSlug,
                        'tab'  => $tab->getSlug(),
                    ], admin_url('options-general.php')) ?>"
                    class="<?php echo implode(' ', [
                        'pb-2 border-b-3 px-3.5',
                        'whitespace-nowrap text-sm font-medium',
                        $this->currentTab->getSlug() === $tab->getSlug()
                            ? 'border-primary text-primary'
                            : 'text-littleMuted border-transparent hover:border-gray-300 hover:text-gray-700'
                    ]) ?>"
                >
                    <?php if ($icon = $tab->getIcon()) echo $icon(['class' => 'mr-1 inline-block h-4 w-4']) ?>
                    <?php echo $tab->getName() ?>
                </a>
            <?php endforeach ?>
        </nav>
        <?php
    }
}
