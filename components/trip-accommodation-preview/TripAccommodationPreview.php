<?php

namespace Gust\Components;

use Gust\ComponentBase;

class TripAccommodationPreview extends ComponentBase
{
    protected static string $name = 'trip-accommodation-preview';

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

        return ! empty(\get_field('accommodation', $postId));
    }

    protected static function transform(array $args): array
    {
        $postId = ! empty($args['post_id']) ? (int) $args['post_id'] : \get_the_ID();
        $accommodation = \get_field('accommodation', $postId);

        if (! $accommodation instanceof \WP_Post) {
            return $args;
        }

        $args['title'] = $accommodation->post_title;
        $args['url'] = \get_permalink($accommodation->ID);
        $args['star_rating'] = (int) (\get_field('star_rating', $accommodation->ID) ?: 0);
        $args['tags'] = array_map(fn ($tag) => $tag['label'] ?? '', \get_field('tags', $accommodation->ID) ?: []);
        $args['description'] = \get_field('description', $accommodation->ID) ?: '';
        $args['rooms_intro'] = \get_field('rooms_intro', $accommodation->ID) ?: '';

        $gallery = \get_field('summary_gallery', $accommodation->ID) ?: [];
        $args['gallery'] = array_map(function ($image) {
            $id = is_array($image) ? ($image['ID'] ?? null) : $image;

            return ! empty($id) ? Image::make(id: $id, size: 'gust_card_square', sizes: '(min-width: 768px) 33vw, 100vw') : null;
        }, $gallery);
        $args['gallery'] = array_values(array_filter($args['gallery']));

        return $args;
    }
}
