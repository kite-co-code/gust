<?php

namespace Gust\Components;

use Gust\ComponentBase;

class Quote extends ComponentBase
{
    protected static string $name = 'quote';

    public static function make(
        array $classes = [],
        string $quote = '',
        string $credit = '',
        string $role = '',
        ...$others
    ): ?static {
        return static::createFromArgs(static::mergeArgs(get_defined_vars()));
    }

    protected static function validate(array $args): bool
    {
        return ! empty(trim((string) ($args['quote'] ?? '')));
    }

    protected static function transform(array $args): array
    {
        $args['classes'] = array_merge(['quote'], $args['classes'] ?? []);

        return $args;
    }
}
