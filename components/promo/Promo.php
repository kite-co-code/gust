<?php

namespace Gust\Components;

use Gust\ComponentBase;

class Promo extends ComponentBase
{
    protected static string $name = 'promo';

    public static function make(
        string $title = '',
        string $subheading = '',
        ?array $link = null,
        int $post_id = 0,
        array $classes = [],
        ...$others
    ): ?static {
        return static::createFromArgs(static::mergeArgs(get_defined_vars()));
    }

    protected static function validate(array $args): bool
    {
        // When used via post_id, read from ACF before validating
        if (! empty($args['post_id']) && empty($args['title'])) {
            $title = \get_field('promo_title', (int) $args['post_id']);
            return ! empty($title);
        }

        return ! empty($args['title']);
    }

    protected static function transform(array $args): array
    {
        if (! empty($args['post_id']) && empty($args['title'])) {
            $post_id = (int) $args['post_id'];

            $args['title']      = \get_field('promo_title', $post_id) ?: '';
            $args['subheading'] = \get_field('promo_subheading', $post_id) ?: '';
            $args['link']       = \get_field('promo_link', $post_id) ?: null;
        }

        return $args;
    }
}
