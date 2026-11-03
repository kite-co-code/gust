<?php

namespace Gust\Components;

use Gust\ComponentBase;

/**
 * Burger Component
 *
 * A decorative hamburger icon. Put it inside a <button> and set the
 * accessible name, aria-controls/aria-expanded or popovertarget on that
 * button, not on the icon.
 *
 * Usage:
 *   use Gust\Components\Burger;
 *
 *   echo Burger::make(attributes: ['aria-hidden' => 'true']);
 */
class Burger extends ComponentBase
{
    protected static string $name = 'burger';

    /**
     * Create a new Burger component.
     *
     * @return static|null Returns null if component should not render.
     */
    public static function make(
        array $classes = [],
        array $attributes = [],
        ...$others
    ): ?static {
        return static::createFromArgs(static::mergeArgs(get_defined_vars()));
    }
}
