<?php

namespace Gust\Components;

use Gust\ComponentBase;

class TripHighlights extends ComponentBase
{
    protected static string $name = 'trip-highlights';

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

        return ! empty(\get_field('highlights', $postId));
    }

    protected static function transform(array $args): array
    {
        $postId = ! empty($args['post_id']) ? (int) $args['post_id'] : \get_the_ID();
        $highlights = \get_field('highlights', $postId) ?: [];

        $args['items'] = array_values(array_filter(array_map(function (array $item) {
            if (empty($item['heading']) && empty($item['description']) && empty($item['image'])) {
                return null;
            }

            return [
                'heading' => $item['heading'] ?? '',
                'description' => $item['description'] ?? '',
                'image' => ! empty($item['image']) ? Image::make(id: $item['image']['ID'] ?? $item['image']['id'], size: 'gust_card_square', sizes: '(min-width: 768px) 25vw, 100vw') : null,
            ];
        }, $highlights)));

        return $args;
    }
}
