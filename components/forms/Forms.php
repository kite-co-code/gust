<?php

namespace Gust\Components;

use Gust\Component;
use Gust\ComponentBase;

/**
 * Forms Component
 *
 * Usage:
 *   use Gust\Components\Forms;
 *
 *   echo Forms::make();
 */
class Forms extends ComponentBase
{
    protected static string $name = 'forms';

    /**
     * Create a new Forms component.
     *
     * @return static|null Returns null if component should not render.
     */
    public static function make(
        array $classes = [],
        ...$others
    ): ?static {
        return static::createFromArgs(static::mergeArgs(get_defined_vars()));
    }
}
