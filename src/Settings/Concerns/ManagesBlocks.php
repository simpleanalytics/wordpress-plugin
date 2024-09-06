<?php

namespace SimpleAnalytics\Settings\Concerns;

use SimpleAnalytics\Settings\Blocks\Block;
use SimpleAnalytics\Settings\Blocks\CalloutBlock;

trait ManagesBlocks
{
    abstract protected function addBlock(Block $block): self;

    public function callout(string $text): CalloutBlock
    {
        $block = new CalloutBlock($text);
        $this->addBlock($block);
        return $block;
    }
}
