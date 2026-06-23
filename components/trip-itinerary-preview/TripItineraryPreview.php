<?php

namespace Gust\Components;

use Gust\ComponentBase;

class TripItineraryPreview extends ComponentBase
{
    protected static string $name = 'trip-itinerary-preview';

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

        return ! empty(\get_field('itinerary', $postId));
    }

    protected static function transform(array $args): array
    {
        $postId = ! empty($args['post_id']) ? (int) $args['post_id'] : \get_the_ID();
        $itinerary = \get_field('itinerary', $postId);

        if ($itinerary instanceof \WP_Post) {
            $args['title'] = $itinerary->post_title;
            $args['url'] = \get_permalink($itinerary->ID);
            $previewDays = \get_field('preview_days', $itinerary->ID) ?: [];
            $previewDays = array_slice($previewDays, 0, 3);

            $args['items'] = array_values(array_filter(array_map(static function (array $day, int $index): ?array {
                $title = trim((string) ($day['title'] ?? ''));
                $summary = $day['summary'] ?? '';

                if ($title === '' || $summary === '') {
                    return null;
                }

                return [
                    'day' => $index + 1,
                    'title' => $title,
                    'summary' => $summary,
                ];
            }, $previewDays, array_keys($previewDays))));

            if (empty($args['items'])) {
                $args['preview'] = \has_excerpt($itinerary->ID)
                    ? \get_the_excerpt($itinerary->ID)
                    : \wp_trim_words(\wp_strip_all_tags($itinerary->post_content), 60);
            }
        }

        return $args;
    }
}
