<?php

namespace Theme\Modules\Guides;

class PostType
{
    protected const SLUG = 'guide';

    public static function init(): void
    {
        \add_action('init', [__CLASS__, 'register']);
        \add_filter('gust/templates/post-types', [__CLASS__, 'filterGustTemplatesPostTypes']);
    }

    public static function register(): void
    {
        if (! function_exists('register_extended_post_type')) {
            return;
        }

        \register_extended_post_type(self::SLUG, [
            'public' => true,
            'has_archive' => false,
            'hierarchical' => false,
            'show_in_rest' => true,
            'menu_position' => 20,
            'menu_icon' => 'dashicons-id-alt',
            'enter_title_here' => 'Guide Name',
            'supports' => [
                'title',
                'editor',
                'thumbnail',
                'revisions',
                'custom-fields',
            ],
            'admin_cols' => [
                'thumbnail' => [
                    'title' => 'Thumbnail',
                    'featured_image' => 'thumbnail',
                    'width' => 80,
                    'height' => 80,
                ],
                'title' => ['title' => 'Name'],
                'updated' => [
                    'title' => 'Updated',
                    'post_field' => 'post_modified',
                    'date_format' => 'Y/m/d',
                ],
            ],
        ], [
            'singular' => __('Guide', 'gust'),
            'plural' => __('Guides', 'gust'),
            'slug' => self::SLUG,
        ]);
    }

    public static function filterGustTemplatesPostTypes(array $postTypes): array
    {
        $postTypes[] = self::SLUG;

        return $postTypes;
    }
}
