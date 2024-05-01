<?php

namespace SimpleAnalytics\Admin\Fields;

class CheckboxSelect extends Field
{
    protected array $options;

    public function render($value)
    {
        foreach ($this->options as $option_value => $option_label) {
            ?>
            <label>
                <input
                    type="checkbox"
                    name="<?= $this->name ?>[]"
                    value="<?= $option_value ?>"
                    <?= in_array($option_value, $value) ? 'checked="checked"' : '' ?>
                />
                <?php echo esc_html($option_label) ?>
            </label>
            <br>
            <?php
        }
    }
}
