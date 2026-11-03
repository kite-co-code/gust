<?php

namespace Gust\Components;

use Gust\Component;
use Gust\ComponentBase;

/**
 * Accordion Component
 *
 * Usage:
 *   use Gust\Components\Accordion;
 *
 *   echo Accordion::make();
 */
class Accordion extends ComponentBase
{
    protected static string $name = 'accordion';

    /**
     * Create a new Accordion component.
     *
     * @return static|null Returns null if component should not render.
     */
    public static function make(
        array $classes = [],
        ...$others
    ): ?static {
        return static::createFromArgs(static::mergeArgs(get_defined_vars()));
    }

    protected static function transform(array $args): array
    {
        if (! empty($args['heading'])) {
            $args['heading'] = [
                'heading' => $args['heading'],
                'classes' => ['accordion__heading'],
            ];
        }

        if (! empty($args['accordion_items'])) {
            $group_id = wp_unique_id('accordion-group-');

            $args['accordion_items'] = array_map(function ($item) use ($group_id) {
                $item['panel_id'] = wp_unique_id('accordion-panel-');
                $item['button_id'] = wp_unique_id('accordion-button-');

                // The heading wraps the button (APG accordion pattern).
                $item['button_attributes'] = [
                    'id' => $item['button_id'],
                    'class' => 'accordion__item__header',
                    'type' => 'button',
                    'aria-expanded' => 'false',
                    'aria-controls' => $item['panel_id'],
                ];

                $item['panel_attributes'] = [
                    'id' => $item['panel_id'],
                    'class' => 'accordion__item__panel',
                    'role' => 'region',
                    'hidden' => true,
                    'aria-hidden' => 'true',
                    'aria-labelledby' => $item['button_id'],
                    'data-disclosure' => true,
                    'data-disclosure-animate' => 'true',
                    'data-disclosure-group' => $group_id,
                ];

                return $item;
            }, $args['accordion_items']);
        }

        return $args;
    }
}
