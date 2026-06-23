<?php

namespace Gust\Components;

use Gust\ComponentBase;

class Stars extends ComponentBase
{
    protected static string $name = 'stars';

    public static function make(
        array $classes = [],
        ...$others
    ): ?static {
        return static::createFromArgs(static::mergeArgs(get_defined_vars()));
    }

    protected static function validate(array $args): bool
    {
        $stars = $args['stars'] ?? 0;
        return is_numeric($stars) && $stars >= 0 && $stars <= 5;
    }

    protected static function transform(array $args): array
    {
        $args['classes'] = array_merge(['stars'], $args['classes'] ?? []);

        return $args;
    }
}
