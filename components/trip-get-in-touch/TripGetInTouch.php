<?php

namespace Gust\Components;

use Gust\ComponentBase;

class TripGetInTouch extends ComponentBase
{
    protected static string $name = 'trip-get-in-touch';

    public static function make(
        int $post_id = 0,
        array $classes = [],
        ...$others
    ): ?static {
        return static::createFromArgs(static::mergeArgs(get_defined_vars()));
    }

    protected static function validate(array $args): bool
    {
        $contacts = \get_field('get_in_touch_contacts', 'option');

        return ! empty($contacts);
    }

    protected static function transform(array $args): array
    {
        $contacts = \get_field('get_in_touch_contacts', 'option') ?: [];

        $args['contacts'] = array_map(fn (array $row) => [
            'icon' => $row['icon'] ?? 'phone',
            'label' => $row['label'] ?? '',
            'value' => $row['value'] ?? '',
            'url' => $row['url'] ?? '',
        ], $contacts);

        return $args;
    }
}
