<?php

namespace Gust\Components;

use Gust\ComponentBase;

/**
 * TestimonialCard Component
 *
 * Individual testimonial card with stars, quote, and author attribution.
 * Used by TestimonialCards container.
 *
 * Usage:
 *   use Gust\Components\TestimonialCard;
 *
 *   echo TestimonialCard::make(
 *       stars: 5,
 *       quote: 'An incredible experience.',
 *       author_name: 'Jane Smith',
 *       author_detail: 'Open Water Swimmer, London',
 *   );
 */
class TestimonialCard extends ComponentBase
{
    protected static string $name = 'testimonial-card';

    public static function make(
        array $classes = [],
        int $stars = 5,
        string $quote = '',
        string $author_name = '',
        string $author_detail = '',
        ?array $image = null,
        ?string $url = null,
        ...$others
    ): ?static {
        return static::createFromArgs(static::mergeArgs(get_defined_vars()));
    }

    protected static function validate(array $args): bool
    {
        return ! empty($args['quote']);
    }

    protected static function transform(array $args): array
    {
        $args['classes'] ??= [];

        if (! empty($args['url'])) {
            $args['classes'][] = 'has-link';
        }

        return $args;
    }
}
