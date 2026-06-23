<?php

namespace Gust\Components;

use Gust\ComponentBase;

class TextItems extends ComponentBase
{
    protected static string $name = 'text-items';

    public static function make(
        string $heading = '',
        array $items = [],
        array $classes = [],
        ...$others
    ): ?static {
        return static::createFromArgs(static::mergeArgs(get_defined_vars()));
    }

    protected static function validate(array $args): bool
    {
        return ! empty($args['items']);
    }

    protected static function transform(array $args): array
    {
        $rawItems = $args['items'] ?? [];

        $args['items'] = array_values(array_filter(array_map(static function (array $row): ?array {
            $title       = trim((string) ($row['title'] ?? ''));
            $description = $row['description'] ?? '';

            if ($title === '' && $description === '') {
                return null;
            }

            return [
                'meta'        => trim((string) ($row['meta'] ?? '')),
                'title'       => $title,
                'description' => $description,
            ];
        }, $rawItems)));

        return $args;
    }
}
