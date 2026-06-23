<?php

namespace Gust\Components;

use Gust\ComponentBase;

class CalendarListings extends ComponentBase
{
    protected static string $name = 'calendar-listings';

    public static function make(
        array $groups = [],
        array $classes = [],
        ...$others
    ): ?static {
        return static::createFromArgs(static::mergeArgs(get_defined_vars()));
    }

    protected static function validate(array $args): bool
    {
        return ! empty($args['groups']);
    }

    protected static function transform(array $args): array
    {
        $args['classes'] = array_merge(['calendar-listings'], $args['classes'] ?? []);

        return $args;
    }
}
