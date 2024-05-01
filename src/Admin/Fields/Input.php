<?php

namespace SimpleAnalytics\Admin\Fields;

class Input extends Field
{
    public function render($value)
    {
        ?>
        <input type="text" name="<?= $this->name ?>" value="<?= $value ?>"/>
        <?php
    }
}
