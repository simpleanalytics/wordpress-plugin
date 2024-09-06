<?php

namespace SimpleAnalytics\UI;

readonly class LabelComponent
{
    public function __construct(
        private string  $value,
        private ?string $docs,
        private ?string $for = null,
        private string  $as = 'label',
    ) {
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
            $this->formatAttributes($attributes),
            esc_html($this->value),
            $this->docs ? $this->renderDocsLink() : ''
        );
    }

    protected function formatAttributes(array $attributes): string
    {
        return implode(' ', array_map(function ($key, $value) {
            return sprintf('%s="%s"', $key, esc_attr($value));
        }, array_keys($attributes), $attributes));
    }

    protected function renderDocsLink(): string
    {
        return sprintf(
            ' <a href="%s" target="_blank" class="text-primary">(docs)</a>',
            esc_url($this->docs)
        );
    }
}
