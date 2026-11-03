<?php

namespace Gust\Tests\Fixtures;

use Gust\ComponentBase;

/**
 * A minimal component for testing ComponentBase's arg processing.
 *
 * - Defaults: heading, level and classes.
 * - Validation: the heading must not be empty.
 * - Transform: adds a level modifier class.
 *
 * Each hook records the args it received in static::$calls, so tests can
 * check the order (defaults -> validate -> transform).
 */
class FakeComponent extends ComponentBase
{
    protected static string $name = 'fake';

    /** @var array<int, array{0: string, 1: array}> */
    public static array $calls = [];

    public static function make(
        ?string $heading = null,
        ?int $level = null,
        ?array $classes = null,
        ...$others
    ): ?static {
        return static::createFromArgs(static::mergeArgs(get_defined_vars()));
    }

    protected static function getDefaults(): array
    {
        return [
            'heading' => 'Default heading',
            'level' => 2,
            'classes' => [],
        ];
    }

    protected static function validate(array $args): bool
    {
        static::$calls[] = ['validate', $args];

        return $args['heading'] !== '';
    }

    protected static function transform(array $args): array
    {
        static::$calls[] = ['transform', $args];

        $args['classes'][] = 'fake--level-'.$args['level'];

        return $args;
    }
}
