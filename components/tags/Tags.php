<?php

namespace Gust\Components;

use Gust\ComponentBase;

class Tags extends ComponentBase
{
    protected static string $name = 'tags';

    public static function make(
        array $tags = [],
        array $classes = [],
        ?string $variant = null,
        ...$others
    ): ?static {
        return static::createFromArgs(static::mergeArgs(get_defined_vars()));
    }

    protected static function validate(array $args): bool
    {
        return ! empty($args['tags']);
    }

    protected static function transform(array $args): array
    {
        $args['classes'] = array_merge(['tags'], $args['classes'] ?? []);

        $args['tags'] = array_values(array_filter(array_map(function ($tag) {
            if (is_string($tag) && '' !== trim($tag)) {
                return trim($tag);
            }

            return null;
        }, $args['tags'] ?? [])));

        if (! empty($args['variant'])) {
            $args['classes'][] = "tags--{$args['variant']}";
        }

        return $args;
    }
}
