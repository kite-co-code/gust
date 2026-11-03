<?php

namespace Gust\Components;

use Gust\Component;
use Gust\ComponentBase;

/**
 * Element Component
 *
 * Usage:
 *   use Gust\Components\Element;
 *
 *   echo Element::make();
 */
class Element extends ComponentBase
{
    protected static string $name = 'element';

    /**
     * Tag names Element (and other components with a configurable wrapper element) may output.
     * Extend with the 'gust/component/element/allowed_tags' filter.
     */
    public const ALLOWED_TAGS = [
        'div', 'span', 'p', 'section', 'article', 'aside', 'header', 'footer', 'main', 'nav',
        'figure', 'figcaption', 'blockquote', 'address',
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'ul', 'ol', 'li', 'dl', 'dt', 'dd',
        'strong', 'em', 'b', 'i', 'small', 'mark', 'abbr', 'cite', 'q', 'code', 'pre', 'time', 'sub', 'sup',
        'label', 'legend', 'fieldset',
    ];

    protected static function getDefaults(): array
    {
        return [
            'el' => 'div',
            'content_filter' => 'wp_kses_post',
        ];
    }

    /**
     * Create a new Element component.
     *
     * @return static|null Returns null if component should not render.
     */
    public static function make(
        ?string $el = null,
        array $classes = [],
        ?string $content = null,
        ?string $content_filter = null,
        ...$others
    ): ?static {
        return static::createFromArgs(static::mergeArgs(get_defined_vars()));
    }

    protected static function transform(array $args): array
    {
        $args['el'] = static::tag($args['el']);

        return $args;
    }

    /**
     * Sanitise a tag name, falling back when it isn't in the allowlist.
     *
     * @param  string|null  $el  The requested tag name.
     * @param  string  $fallback  The tag to use when $el isn't allowed.
     * @param  array|null  $allowed  Tag names to allow. Defaults to ALLOWED_TAGS.
     */
    public static function tag(?string $el, string $fallback = 'div', ?array $allowed = null): string
    {
        $allowed ??= \apply_filters('gust/component/element/allowed_tags', static::ALLOWED_TAGS);
        $el = \tag_escape((string) $el);

        return in_array($el, $allowed, true) ? $el : $fallback;
    }
}
