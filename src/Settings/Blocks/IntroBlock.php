<?php

namespace SimpleAnalytics\Settings\Blocks;

use SimpleAnalytics\Settings\Block;

class IntroBlock implements Block
{
    /** @var string[] */
    private $paragraphs;

    /** @param string[] $paragraphs */
    public function __construct(array $paragraphs)
    {
        $this->paragraphs = $paragraphs;
    }

    public function render(): void
    {
        $allowed = ['strong' => [], 'em' => [], 'a' => ['href' => [], 'target' => [], 'rel' => []]];
        ?>
        <div class="space-y-3">
            <?php foreach ($this->paragraphs as $paragraph): ?>
                <p class="text-sm text-gray-600"><?php echo wp_kses($paragraph, $allowed); ?></p>
            <?php endforeach; ?>
        </div>
        <?php
    }
}
