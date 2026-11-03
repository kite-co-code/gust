<?php

namespace Gust;

/**
 * Laravel-style Vite integration for WordPress
 *
 * Handles asset loading with HMR support using the hot file approach.
 */
class Vite
{
    /**
     * The decoded Vite manifest, cached for the request. Null until first read.
     */
    private static ?array $manifest = null;

    /**
     * Script handles that are output with type="module".
     *
     * @var array<string, true>
     */
    private static array $moduleHandles = [];

    /**
     * Check if Vite dev server is running by looking for hot file.
     */
    public static function isRunning(): bool
    {
        if (\wp_get_environment_type() !== 'development') {
            return false;
        }

        $hotFile = \Gust\Paths::assetPath('hot');

        return file_exists($hotFile);
    }

    /**
     * Get the dev server URL from hot file.
     */
    public static function getDevServerUrl(): ?string
    {
        if (! self::isRunning()) {
            return null;
        }

        $hotFile = \Gust\Paths::assetPath('hot');
        $url = trim(file_get_contents($hotFile));

        // Normalize IPv6 localhost to avoid mixed content issues on HTTPS sites
        $url = str_replace(['http://[::1]', 'http://[::]:'], 'http://localhost', $url);

        return $url ?: 'http://localhost:5173';
    }

    /**
     * Get the decoded Vite manifest (public/build/manifest.json).
     *
     * Read and decoded once per request.
     *
     * @return array The manifest, or an empty array if it's missing or invalid.
     */
    public static function manifest(): array
    {
        if (self::$manifest !== null) {
            return self::$manifest;
        }

        $manifestPath = \Gust\Paths::assetPath('build/manifest.json');
        $content = is_readable($manifestPath) ? file_get_contents($manifestPath) : false;
        $manifest = $content ? json_decode($content, true) : null;

        return self::$manifest = is_array($manifest) ? $manifest : [];
    }

    /**
     * Get source path from asset name.
     *
     * Maps known asset names to their source paths. For unknown assets
     * (like component assets), returns the path as-is.
     *
     * @param  string  $asset  Asset name or path (e.g., 'main.js' or 'assets/components/header/scripts-main.js')
     * @return string Source path (e.g., 'assets/main.js')
     */
    public static function getSourcePath(string $asset): string
    {
        // Known main asset mappings
        $mappings = [
            'main.js' => 'assets/main.js',
            'main.css' => 'assets/main.pcss',
            'main-styles.css' => 'assets/main.pcss',
            'editor-scripts.js' => 'assets/editor-scripts.js',
            'editor-styles.css' => 'assets/editor-styles.pcss',
            'admin-scripts.js' => 'assets/admin-scripts.js',
        ];

        // Return mapped path if it exists, otherwise return asset as-is
        // This allows component assets to be passed through directly
        return $mappings[$asset] ?? $asset;
    }

    /**
     * Get asset URL for a given entry point or static asset.
     *
     * @param  string  $entry  Entry point path (e.g., 'assets/main.js') or static asset path (e.g., 'images/logo.svg')
     */
    public static function asset(string $entry): ?string
    {
        if (self::isRunning()) {
            return self::getDevServerUrl().'/'.$entry;
        }

        // Static assets (from assets/static/) don't have 'assets/' prefix
        // They're copied directly to public/build/ by Vite
        if (! str_starts_with($entry, 'assets/')) {
            return \Gust\Paths::assetURL('build/'.$entry);
        }

        // Compiled assets are in the manifest
        $manifest = self::manifest();
        if (isset($manifest[$entry])) {
            return \Gust\Paths::assetURL('build/'.$manifest[$entry]['file']);
        }

        return null;
    }

    /**
     * Enqueue a stylesheet from Vite.
     *
     * @param  string  $handle  WP style handle
     * @param  string  $entry  Entry point path (e.g., 'assets/main.pcss')
     * @param  array  $deps  Dependencies
     */
    public static function enqueueStyle(string $handle, string $entry, array $deps = []): void
    {
        if (self::isRunning()) {
            self::enqueueViteClient();
            $url = self::getDevServerUrl().'/'.$entry;
            \wp_enqueue_style($handle, $url, $deps, null);
        } else {
            $manifest = self::manifest();
            if (! isset($manifest[$entry])) {
                return;
            }

            $manifestEntry = $manifest[$entry];
            $url = \Gust\Paths::assetURL('build/'.$manifestEntry['file']);
            \wp_enqueue_style($handle, $url, $deps, null);
        }
    }

    /**
     * Enqueue a script from Vite as an ES module.
     *
     * Module scripts are deferred by the browser, so no loading strategy is set.
     *
     * @param  string  $handle  WP script handle
     * @param  string  $entry  Entry point path (e.g., 'assets/main.js')
     * @param  array  $deps  Dependencies
     */
    public static function enqueueScript(string $handle, string $entry, array $deps = []): void
    {
        if (self::isRunning()) {
            self::enqueueViteClient();
            $url = self::getDevServerUrl().'/'.$entry;
            \wp_enqueue_script($handle, $url, $deps, null, ['in_footer' => true]);
            self::addModuleHandle($handle);

            return;
        }

        $manifest = self::manifest();
        if (! isset($manifest[$entry])) {
            return;
        }

        $manifestEntry = $manifest[$entry];
        $url = \Gust\Paths::assetURL('build/'.$manifestEntry['file']);
        \wp_enqueue_script($handle, $url, $deps, null, ['in_footer' => true]);
        self::addModuleHandle($handle);

        // Preload imports
        if (isset($manifestEntry['imports'])) {
            foreach ($manifestEntry['imports'] as $import) {
                if (isset($manifest[$import])) {
                    $importUrl = \Gust\Paths::assetURL('build/'.$manifest[$import]['file']);
                    \add_action('wp_head', function () use ($importUrl) {
                        printf('<link rel="modulepreload" href="%s" />'."\n", esc_url($importUrl));
                    }, 2);
                }
            }
        }

        // Enqueue CSS bundled with JS entry
        if (isset($manifestEntry['css'])) {
            foreach ($manifestEntry['css'] as $index => $css) {
                $cssUrl = \Gust\Paths::assetURL('build/'.$css);
                \wp_enqueue_style($handle.'-css-'.$index, $cssUrl, [], null);
            }
        }
    }

    /**
     * Add type="module" to the script tags of tracked module handles.
     *
     * @param  string  $tag  The script tag HTML.
     * @param  string  $handle  The script's registered handle.
     */
    public static function addModuleType(string $tag, string $handle): string
    {
        if (! isset(self::$moduleHandles[$handle])) {
            return $tag;
        }

        return str_replace(' src=', ' type="module" src=', $tag);
    }

    /**
     * Track a script handle as an ES module. Registers the tag filter once.
     */
    private static function addModuleHandle(string $handle): void
    {
        if (empty(self::$moduleHandles)) {
            \add_filter('script_loader_tag', [__CLASS__, 'addModuleType'], 10, 2);
        }

        self::$moduleHandles[$handle] = true;
    }

    /**
     * Enqueue Vite client for HMR (only once).
     */
    private static function enqueueViteClient(): void
    {
        static $enqueued = false;
        if ($enqueued) {
            return;
        }
        $enqueued = true;

        $devServerUrl = self::getDevServerUrl();
        \wp_enqueue_script('vite-client', $devServerUrl.'/@vite/client', [], null, false);
        self::addModuleHandle('vite-client');
    }
}
