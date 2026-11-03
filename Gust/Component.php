<?php

namespace Gust;

/**
 * Framework-level component setup.
 *
 * This class handles:
 * - ACF block registration from components' block.json files
 * - Loading/saving ACF block field group JSON in component directories
 * - Common class/attribute args via the `gust/component/after_filters` filter
 *
 * For creating and rendering components, use typed classes extending ComponentBase.
 *
 * @see \Gust\ComponentBase
 */
class Component
{
    /**
     * Initialise class to set up hooks and filters for all Components.
     */
    public static function init(): void
    {
        // Fallback autoloader: catches unresolved Gust\Components\* classes and hints to run composer dump-autoload.
        spl_autoload_register(function (string $class): void {
            if (! str_starts_with($class, 'Gust\\Components\\')) {
                return;
            }
            $shortName = substr($class, strlen('Gust\\Components\\'));
            $kebab = strtolower(preg_replace('/([A-Z])/', '-$1', lcfirst($shortName)));
            throw new \RuntimeException(
                "Component class \"{$class}\" not found. ".
                'Run `composer dump-autoload` to regenerate the classmap. '.
                "Expected: components/{$kebab}/{$shortName}.php"
            );
        });

        // Set class args for components.
        \add_filter('gust/component/after_filters', [__CLASS__, 'buildComponentClasses'], 10);

        // Set attribute args for components.
        \add_filter('gust/component/after_filters', [__CLASS__, 'buildComponentAttributes'], 20);

        // ACF block registration.
        \add_action('init', [__CLASS__, 'addBlocks'], 5);
        \add_filter('acf/settings/load_json', [__CLASS__, 'loadBlockFieldGroupJSON']);
        \add_action('acf/update_field_group', [__CLASS__, 'saveBlockFieldGroupJSON'], 1);
    }

    public static function addBlocks(): void
    {
        foreach (glob(\get_theme_file_path('components/*/block.json')) as $blockJson) {
            \register_block_type(\dirname($blockJson));
        }
    }

    /**
     * Load ACF block field groups from components' JSON files.
     */
    public static function loadBlockFieldGroupJSON(array $paths): array
    {
        return array_merge(
            $paths,
            glob(\get_theme_file_path('components/*'))
        );
    }

    /**
     * Save each ACF block's field group JSON file into their related components' directory.
     */
    public static function saveBlockFieldGroupJSON($group): void
    {
        if (empty($group['location'][0][0]['param']) || $group['location'][0][0]['param'] !== 'block') {
            return;
        }

        $blockName = preg_replace('#^(acf|theme)/#', '', $group['location'][0][0]['value']);
        $blockFieldGroupJSONDirPaths = glob(\get_theme_file_path("components/$blockName"));

        if (! is_array($blockFieldGroupJSONDirPaths)) {
            return;
        }

        foreach ($blockFieldGroupJSONDirPaths as $path) {
            if (! is_dir($path)) {
                continue;
            }

            add_action('acf/settings/save_json', function () use ($path) {
                return $path;
            }, 9999);
        }
    }

    /**
     * Adds common HTML classes based on the given Component args.
     */
    public static function buildComponentClasses(?array $args): ?array
    {
        if ($args === null) {
            return $args;
        }

        $args['classes'] = $args['classes'] ?? [];

        if (! empty($args['align'])) {
            $args['classes'][] = 'align'.$args['align'];
        }

        if (! empty($args['background_color']) && $args['background_color'] !== 'none') {
            $args['classes'][] = 'has-background';
            $args['classes'][] = 'has-'.$args['background_color'].'-background-color';
        }

        return $args;
    }

    /**
     * Adds required properties to the $args['attributes'] array.
     */
    public static function buildComponentAttributes(?array $args): ?array
    {
        if ($args === null) {
            return $args;
        }

        $args['attributes'] = $args['attributes'] ?? [];

        if (! is_array($args['attributes'])) {
            return $args;
        }

        if (! empty($args['id'])) {
            $args['attributes']['id'] = $args['id'];
        }

        return $args;
    }
}
