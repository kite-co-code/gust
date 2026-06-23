<?php

namespace Gust\Components;

use Gust\ComponentBase;

class TripRelatedStories extends ComponentBase
{
    protected static string $name = 'trip-related-stories';

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

        return ! empty(\get_field('related_stories', $postId));
    }

    protected static function transform(array $args): array
    {
        $postId = ! empty($args['post_id']) ? (int) $args['post_id'] : \get_the_ID();
        $stories = \get_field('related_stories', $postId) ?: [];

        $args['items'] = array_map(fn ($story) => ['object' => $story], $stories);

        return $args;
    }
}
