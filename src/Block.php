<?php

namespace Takt\Score;

use Illuminate\Support\Str;

class Block
{
    protected $anchor;

    protected \WP_Block $block;

    public $attributes = [];

    public function __construct(\WP_Block $block)
    {
        $this->block = $block;
        $this->attributes = $block->attributes;
        $this->setAnchor();
    }

    protected function setAnchor()
    {
        $this->anchor = $block->anchor ?? null;

        if (empty($anchor)) {
            $this->anchor =
                'auto-' . Str::slug($this->block->name) . '-' . uniqid();
        }
    }

    public function props($classes = [], $props = [])
    {
        $args = [];

        // Append ID
        $args['id'] = esc_attr($this->anchor);

        // Append Aria Labelledby
        if (
            !empty($this->attributes['heading']) &&
            !isset($props['aria-labelledby'])
        ) {
            $args['aria-labelledby'] = $this->anchor . '-heading';
        }

        // Normalize $classes to array with keys
        if (!is_array($classes)) {
            $classes = [$classes => true];
        }

        // Append class list
        $classList = class_name($classes);
        if (!empty($classList)) {
            $args['class'] = esc_attr($classList);
        }

        // Normalize $props if it's a string
        if (is_string($props)) {
            $attrs = [];
            preg_match_all(
                '/([a-zA-Z0-9_\-:]+)\s*=\s*"([^"]*)"/',
                $props,
                $matches,
                PREG_SET_ORDER,
            );
            foreach ($matches as $match) {
                $attrs[$match[1]] = $match[2];
            }
            $props = $attrs;
        }

        // Append the props
        if (is_array($props) && !empty($props)) {
            $args = array_merge($props, $args);
            $args = array_filter($args, function ($value) {
                return $value !== false && $value !== null;
            });
        }

        // Output
        return get_block_wrapper_attributes($args);
    }
}
