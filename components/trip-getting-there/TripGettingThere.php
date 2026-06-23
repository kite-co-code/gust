<?php

namespace Gust\Components;

use Gust\ComponentBase;

class TripGettingThere extends ComponentBase
{
    protected static string $name = 'trip-getting-there';

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

        return ! empty(\get_field('getting_there_stages', $postId));
    }

    protected static function transform(array $args): array
    {
        $postId = ! empty($args['post_id']) ? (int) $args['post_id'] : \get_the_ID();

        $args['stages'] = \get_field('getting_there_stages', $postId) ?: [];

        return $args;
    }
}
