<?php

namespace Gust\Components;

use Gust\ComponentBase;

class TripIncludes extends ComponentBase
{
    protected static string $name = 'trip-includes';

    public static function make(
        int $post_id = 0,
        array $classes = [],
        ...$others
    ): ?static {
        return static::createFromArgs(static::mergeArgs(get_defined_vars()));
    }

    protected static function validate(array $args): bool
    {
        $postId = ! empty($args['post_id']) ? (int) $args['post_id'] : \get_the_ID();

        return ! empty(\get_field('included_items', $postId)) || ! empty(\get_field('not_included_items', $postId));
    }

    protected static function transform(array $args): array
    {
        $postId = ! empty($args['post_id']) ? (int) $args['post_id'] : \get_the_ID();

        $args['included_items'] = array_map(fn ($item) => $item['label'] ?? '', \get_field('included_items', $postId) ?: []);
        $args['not_included_items'] = array_map(fn ($item) => $item['label'] ?? '', \get_field('not_included_items', $postId) ?: []);

        return $args;
    }
}
