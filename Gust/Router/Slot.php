<?php

namespace Gust\Router;

use Gust\Router;

class Slot
{
    public static function init(): void
    {
        \add_action('acf/init', [static::class, 'registerBlocks']);
        // After Editor::allowedBlockTypes (priority 10) builds the allow-list
        \add_filter('allowed_block_types_all', [static::class, 'restrictToRouterPages'], 20, 2);
        \add_filter('block_categories_all', [static::class, 'registerBlockCategory'], 10, 2);
    }

    public static function registerBlockCategory(array $categories, $context): array
    {
        // Add Theme category if not exists
        $hasTheme = false;
        foreach ($categories as $category) {
            if ($category['slug'] === 'theme') {
                $hasTheme = true;
                break;
            }
        }

        if (! $hasTheme) {
            array_unshift($categories, [
                'slug' => 'theme',
                'title' => \__('Theme', 'gust'),
                'icon' => null,
            ]);
        }

        return $categories;
    }

    public static function registerBlocks(): void
    {
        if (! function_exists('acf_register_block_type')) {
            return;
        }

        // Template Content block
        \acf_register_block_type([
            'name' => 'template-content',
            'title' => \__('Template Content', 'gust'),
            'description' => \__('Displays dynamic page content.', 'gust'),
            'category' => 'theme',
            'icon' => 'layout',
            'apiVersion' => 3,
            'mode' => 'preview',
            'align' => 'full',
            'supports' => [
                'mode' => false,
                'multiple' => false,
                'jsx' => false,
            ],
            'render_callback' => [static::class, 'renderTemplateContent'],
        ]);
    }

    public static function renderTemplateContent(array $block): void
    {
        if (\is_admin() || static::isRestRequest()) {
            echo static::placeholder(\__('Template Content', 'gust'));

            return;
        }

        echo Router::renderSlot('template-content');
    }

    protected static function isRestRequest(): bool
    {
        if (function_exists('wp_is_rest_request')) {
            return \wp_is_rest_request();
        }

        return defined('REST_REQUEST') && REST_REQUEST;
    }

    protected static function placeholder(string $label): string
    {
        return sprintf(
            '<div class="router-slot-placeholder">
                <strong>%s</strong>
                <span>%s</span>
            </div>',
            \esc_html($label),
            \esc_html__('Dynamic content rendered on frontend.', 'gust')
        );
    }

    public static function restrictToRouterPages($allowed, $context): mixed
    {
        $post = $context->post ?? null;

        if (! $post) {
            return $allowed;
        }

        // ACF prefixes PHP-registered block names with `acf/`
        $routerBlocks = ['acf/template-content'];

        // Router pages get router blocks even when an allow-list leaves them out
        if (RouterPage::isRouterPage($post->ID)) {
            return is_array($allowed)
                ? array_values(array_unique([...$allowed, ...$routerBlocks]))
                : $allowed;
        }

        // `true` means all blocks are allowed, so list them to exclude router blocks
        if ($allowed === true) {
            $allowed = array_keys(\WP_Block_Type_Registry::get_instance()->get_all_registered());
        }

        if (is_array($allowed)) {
            return array_values(array_diff($allowed, $routerBlocks));
        }

        return $allowed;
    }
}
