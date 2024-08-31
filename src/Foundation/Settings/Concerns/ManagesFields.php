<?php

namespace SimpleAnalytics\Foundation\Settings\Concerns;

use SimpleAnalytics\Foundation\Settings\Fields\Checkbox;
use SimpleAnalytics\Foundation\Settings\Fields\Field;
use SimpleAnalytics\Foundation\Settings\Fields\Input;

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

//    public function select(string $key, string $label, array $options): self
//    {
//        $this->fields[] = new Fields\Select($key, $label, $options);
//        return $this;
//    }
}
