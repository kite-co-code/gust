<?php

/**
 * Grid — server-side render.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Inner block content.
 * @var WP_Block $block      Block instance.
 */

$columns = $attributes['columns'] ?? 2;
$breakpoint = $attributes['breakpoint'] ?? 'tablet';
$anchor = ! empty($attributes['anchor']) ? ' id="' . esc_attr($attributes['anchor']) . '"' : '';

printf(
    '<div%s class="grid-block grid-simple alignwide" data-cols="%d" data-stack="%s">%s</div>',
    $anchor,
    (int) $columns,
    esc_attr($breakpoint),
    $content
);
