<?php

namespace SimpleAnalytics\Settings\Concerns;

use SimpleAnalytics\Settings\Fields\Checkbox;
use SimpleAnalytics\Settings\Fields\Field;
use SimpleAnalytics\Settings\Fields\Input;
use SimpleAnalytics\Settings\Fields\Checkboxes;
use SimpleAnalytics\Settings\Fields\IpList;

trait ManagesFields
{
    abstract protected function addField(Field $field): self;

    public function input(string $key, string $label): Input
    {
        $field = new Input($key, $label);
        $this->addField($field);
        return $field;
    }

    public function checkbox(string $key, string $label): Checkbox
    {
        $field = new Checkbox($key, $label);
        $this->addField($field);
        return $field;
    }

    public function multiCheckbox(string $key, string $label): Checkboxes
    {
        $field = new Checkboxes($key, $label);
        $this->addField($field);
        return $field;
    }

    public function ipList(string $key, string $label): IpList
    {
        $field = new IpList($key, $label);
        $this->addField($field);
        return $field;
    }
}
