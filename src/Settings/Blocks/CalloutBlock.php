<?php

namespace SimpleAnalytics\Settings\Blocks;

use SimpleAnalytics\Settings\Block;
use function SimpleAnalytics\get_icon;

class CalloutBlock implements Block
{
    /**
     * @readonly
     * @var string
     */
    private $text;
    public function __construct(string $text)
    {
        $this->text = $text;
    }
    public function render(): void
    {
        ?>
        <div class="rounded-md bg-blue-50 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <?php 
        echo get_icon('info')->class('h-5 w-5 text-blue-400');
        ?>
                </div>
                <div class="ml-2.5 flex-1 md:flex md:justify-between">
                    <p class="text-sm text-blue-700"><?php 
        echo htmlspecialchars($this->text);
        ?></p>
                </div>
            </div>
        </div>
        <?php 
    }
}
