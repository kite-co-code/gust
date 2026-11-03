<?php

namespace Gust\Router;

use Gust\Router;

/**
 * Router editor blocks.
 *
 * The Template Content block is `theme/template-content`, registered from
 * Gust/Router/blocks/template-content/block.json. Versions before this used
 * acf_register_block_type(), which named it `acf/template-content`; that name
 * is still registered (hidden from the inserter) so content saved with it
 * keeps rendering. To move old content over, replace `wp:acf/template-content`
 * with `wp:theme/template-content` in post_content.
 */
class Slot
{
    /** Blocks that only make sense on router pages. */
    const ROUTER_BLOCKS = ['theme/template-content', 'acf/template-content'];

    public static function init(): void
    {
        \add_action('init', [static::class, 'registerBlocks'], 5);
        // Late, so it runs after allow lists that add every theme/* block (Gust\WordPress\Editor).
        \add_filter('allowed_block_types_all', [static::class, 'restrictToRouterPages'], 100, 2);
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

        // Template Content block (theme/template-content)
        \register_block_type(__DIR__.'/blocks/template-content');

        // Legacy name, kept so content saved as acf/template-content still renders.
        \acf_register_block_type([
            'name' => 'template-content',
            'title' => \__('Template Content (legacy)', 'gust'),
            'description' => \__('Displays dynamic page content.', 'gust'),
            'category' => 'theme',
            'icon' => 'layout',
            'apiVersion' => 3,
            'mode' => 'preview',
            'align' => 'full',
            'supports' => [
                'inserter' => false,
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

    /**
     * Only offer router blocks in the editor for router pages.
     *
     * @param  bool|string[]  $allowed
     * @return bool|string[]
     */
    public static function restrictToRouterPages($allowed, $context): mixed
    {
        $post = $context->post ?? null;

        if (! $post || RouterPage::isRouterPage($post->ID)) {
            return $allowed;
        }

        // true means "all blocks", so list every registered block instead.
        // Blocks registered only in JavaScript aren't in the registry and drop out.
        if ($allowed === true) {
            $allowed = array_keys(\WP_Block_Type_Registry::get_instance()->get_all_registered());
        }

        if (is_array($allowed)) {
            return array_values(array_diff($allowed, static::ROUTER_BLOCKS));
        }

        return $allowed;
    }
}
