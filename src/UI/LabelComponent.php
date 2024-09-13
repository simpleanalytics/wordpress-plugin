<?php

namespace SimpleAnalytics\UI;

use SimpleAnalytics\Support\Str;

class LabelComponent
{
    /**
     * @readonly
     * @var string
     */
    private $value;
    /**
     * @readonly
     * @var string|null
     */
    private $docs;
    /**
     * @readonly
     * @var string|null
     */
    private $for;
    /**
     * @readonly
     * @var string
     */
    private $as = 'label';
    public function __construct(string  $value, ?string $docs, ?string $for = null, string  $as = 'label')
    {
        $this->value = $value;
        $this->docs = $docs;
        $this->for = $for;
        $this->as = $as;
    }
    public function __invoke(): void
    {
        $attributes = [
            'class' => 'block text-sm font-medium leading-6 text-gray-900',
        ];

        if ($this->as === 'label') {
            $attributes['for'] = esc_attr($this->for);
        }

        echo sprintf(
            '<%1$s %2$s>%3$s%4$s</%1$s>',
            $this->as,
            Str::htmlAttributes($attributes),
            esc_html($this->value),
            $this->docs ? $this->renderDocsLink() : ''
        );
    }

    protected function renderDocsLink(): string
    {
        return sprintf(
            ' <a href="%s" target="_blank" class="text-primary">(docs)</a>',
            esc_url($this->docs)
        );
    }
}
