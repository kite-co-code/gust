<?php

namespace Gust\Components;

use Gust\Component;
use Gust\ComponentBase;

/**
 * TaxonomyFilters Component
 *
 * Usage:
 *   use Gust\Components\TaxonomyFilters;
 *
 *   echo TaxonomyFilters::make(taxonomy: 'category');
 *   echo TaxonomyFilters::make(object: $object); // A WP_Term or WP_Post_Type archive.
 *
 * For a post type archive, the taxonomy is the post type's first public taxonomy
 * ('category' for posts). Change it with the 'gust/taxonomy-filters/post_type_taxonomy' filter.
 */
class TaxonomyFilters extends ComponentBase
{
    protected static string $name = 'taxonomy-filters';

    protected static function getDefaults(): array
    {
        return [
            'show' => true,
        ];
    }

    /**
     * Create a new TaxonomyFilters component.
     *
     * @return static|null Returns null if component should not render.
     */
    public static function make(
        array $classes = [],
        ?int $current_item = null,
        ?string $label = null,
        ?bool $show = null,
        ?string $taxonomy = null,
        ...$others
    ): ?static {
        return static::createFromArgs(static::mergeArgs(get_defined_vars()));
    }

    /**
     * Validate args before rendering.
     */
    protected static function validate(array $args): bool
    {
        return $args['show'] !== false;
    }

    /**
     * Transform args before rendering.
     */
    protected static function transform(array $args): array
    {
        // Default translated value
        $args['label'] ??= __('Filter by', 'gust');
        $args['label_id'] = \wp_unique_id('taxonomy-filters-label-');

        if (! empty($args['object'])) {
            $object = $args['object'];

            if ($object instanceof \WP_Term) {
                $args['taxonomy'] ??= $object->taxonomy;
                $args['current_item'] ??= $object->term_id;
            } elseif ($object instanceof \WP_Post_Type) {
                $args['taxonomy'] ??= static::getPostTypeTaxonomy($object->name);
            }
        }

        if (empty($args['taxonomy'])) {
            return $args;
        }

        $tax = get_taxonomy($args['taxonomy']);

        if (! $tax) {
            return $args;
        }

        $args['items'] = [];

        $args['label'] = sprintf(
            __('Filter by %s', 'gust'),
            strtolower($tax->labels->singular_name)
        );

        $terms = get_terms(['taxonomy' => $args['taxonomy']]);

        if (\is_wp_error($terms) || empty($terms)) {
            return $args;
        }

        foreach ($terms as $term) {
            $url = get_term_link($term);

            if (\is_wp_error($url)) {
                continue;
            }

            $item = [
                'title' => $term->name,
                'url' => $url,
                'classes' => [
                    'btn',
                    'btn--small',
                    'taxonomy-filters__item',
                ],
            ];

            if ((int) $args['current_item'] === $term->term_id) {
                $item['classes'][] = 'taxonomy-filters__item--current';
                $item['attributes']['aria-current'] = 'page';
            }

            $args['items'][] = $item;
        }

        return $args;
    }

    /**
     * The taxonomy to filter a post type archive by: the post type's first public taxonomy.
     *
     * @param  string  $post_type  The post type name.
     * @return string|null The taxonomy name, or null for none.
     */
    protected static function getPostTypeTaxonomy(string $post_type): ?string
    {
        $taxonomies = \get_object_taxonomies($post_type, 'objects');
        $taxonomies = array_filter($taxonomies, fn ($tax) => $tax->public && $tax->publicly_queryable && $tax->name !== 'post_format');

        // Posts have categories and tags; prefer categories.
        $taxonomy = isset($taxonomies['category']) ? 'category' : (array_key_first($taxonomies) ?? null);

        /**
         * Filters the taxonomy used to filter a post type archive.
         *
         * @param  string|null  $taxonomy  The taxonomy name, or null for no filters.
         * @param  string  $post_type  The post type name.
         */
        return \apply_filters('gust/taxonomy-filters/post_type_taxonomy', $taxonomy, $post_type);
    }
}
