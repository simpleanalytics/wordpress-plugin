<?php

namespace SimpleAnalytics\Settings\Concerns;

use SimpleAnalytics\Settings\Blocks\Block;
use SimpleAnalytics\Settings\Blocks\CalloutBlock;
use SimpleAnalytics\Settings\Blocks\Fields\Checkbox;
use SimpleAnalytics\Settings\Blocks\Fields\Checkboxes;
use SimpleAnalytics\Settings\Blocks\Fields\Input;
use SimpleAnalytics\Settings\Blocks\Fields\IpList;

trait ManagesBlocks
{
    abstract protected function addBlock(Block $block): self;

    public function callout(string $text): CalloutBlock
    {
        $block = new CalloutBlock($text);
        $this->addBlock($block);
        return $block;
    }

    public function input(string $key, string $label): Input
    {
        $field = new Input($key, $label);
        $this->addBlock($field);
        return $field;
    }

    public function checkbox(string $key, string $label): Checkbox
    {
        $field = new Checkbox($key, $label);
        $this->addBlock($field);
        return $field;
    }

    public function multiCheckbox(string $key, string $label): Checkboxes
    {
        $field = new Checkboxes($key, $label);
        $this->addBlock($field);
        return $field;
    }

    public function ipList(string $key, string $label): IpList
    {
        $field = new IpList($key, $label);
        $this->addBlock($field);
        return $field;
    }
}
