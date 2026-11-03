<?php

namespace Gust\Dev;

class DevRoutes
{
    public static function init()
    {
        if (\wp_get_environment_type() !== 'development') {
            return;
        }

        \add_action('init', [__CLASS__, 'addRewriteRules']);
        \add_filter('query_vars', [__CLASS__, 'addQueryVars']);
        \add_action('template_redirect', [__CLASS__, 'handleDevRoutes']);
        \add_filter('redirect_canonical', [__CLASS__, 'preventCanonicalRedirect'], 10, 2);

        \add_action('after_switch_theme', [__CLASS__, 'flushRules']);
    }

    protected static function devPageOpen(string $title, string $active_route = ''): void
    {
        $exit_url = \esc_url(\home_url('/'));
        echo '<!DOCTYPE html><html lang="en"><head>';
        echo '<meta charset="UTF-8">';
        echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
        echo '<meta name="robots" content="noindex, nofollow">';
        echo '<title>'.\esc_html($title).' — Dev Kit</title>';
        \wp_head();
        // The Dev Kit's own styles, inlined from a file so they stay isolated from theme styles and don't depend on Vite.
        echo '<style>'.self::devKitStyles().'</style>';
        echo '</head><body>';
        $nav_items = [
            'globals' => 'Globals',
            'utilities' => 'Utilities',
            'components' => 'Components',
            'content' => 'Content',
        ];
        $nav_html = '<nav class="dev-topbar__nav">';
        foreach ($nav_items as $route => $label) {
            $url = \esc_url(\home_url("/_dev/{$route}/"));
            $current = ($active_route === $route || ($route === 'components' && in_array($active_route, ['component', 'all-components']))) ? ' aria-current="page"' : '';
            $nav_html .= "<a href=\"{$url}\"{$current}>{$label}</a>";
        }
        $nav_html .= '</nav>';
        $site_name = \esc_html(\get_bloginfo('name'));
        $site_url = \esc_url(\home_url('/'));
        $dev_url = \esc_url(\home_url('/_dev/'));

        $crumbs = '<nav class="dev-breadcrumb" aria-label="Breadcrumb">';
        $crumbs .= '<a href="'.$site_url.'">'.$site_name.'</a>';
        $crumbs .= '<span class="dev-breadcrumb__sep" aria-hidden="true">›</span>';

        if ($active_route === 'index' || $active_route === '') {
            $crumbs .= '<span class="dev-breadcrumb__current">Dev Kit</span>';
        } else {
            $crumbs .= '<a href="'.$dev_url.'">Dev Kit</a>';
            $crumbs .= '<span class="dev-breadcrumb__sep" aria-hidden="true">›</span>';

            if ($active_route === 'component' || $active_route === 'all-components') {
                $comp_url = \esc_url(\home_url('/_dev/components/'));
                $crumbs .= '<a href="'.$comp_url.'">Components</a>';
                $crumbs .= '<span class="dev-breadcrumb__sep" aria-hidden="true">›</span>';
                $crumbs .= '<span class="dev-breadcrumb__current">'.esc_html($title).'</span>';
            } else {
                $crumbs .= '<span class="dev-breadcrumb__current">'.esc_html($title).'</span>';
            }
        }

        $crumbs .= '</nav>';

        echo '<div class="dev-topbar">'.$crumbs.$nav_html.'</div>';
        echo '<div class="dev-main dev-flow">';
    }

    /**
     * Get the Dev Kit stylesheet (assets/dev-kit.css) for inline output.
     */
    protected static function devKitStyles(): string
    {
        $css = file_get_contents(__DIR__.'/assets/dev-kit.css');

        return $css === false ? '' : $css;
    }

    protected static function devPageClose(): void
    {
        echo '</div>';
        \wp_footer();
        echo '</body></html>';
    }

    public static function addRewriteRules(): void
    {
        \add_rewrite_rule(
            '^_dev/?$',
            'index.php?dev_route=index',
            'top'
        );

        \add_rewrite_rule(
            '^_dev/components/?$',
            'index.php?dev_route=components',
            'top'
        );

        \add_rewrite_rule(
            '^_dev/components/([^/]+)/?$',
            'index.php?dev_route=component&dev_component=$matches[1]',
            'top'
        );

        \add_rewrite_rule(
            '^_dev/globals/?$',
            'index.php?dev_route=globals',
            'top'
        );

        \add_rewrite_rule(
            '^_dev/utilities/?$',
            'index.php?dev_route=utilities',
            'top'
        );

        \add_rewrite_rule(
            '^_dev/content/?$',
            'index.php?dev_route=content',
            'top'
        );

        \add_rewrite_rule(
            '^_dev/all-components/?$',
            'index.php?dev_route=all_components',
            'top'
        );
    }

    public static function addQueryVars(array $vars): array
    {
        $vars[] = 'dev_route';
        $vars[] = 'dev_component';

        return $vars;
    }

    public static function preventCanonicalRedirect($redirect_url, $requested_url)
    {
        if (\get_query_var('dev_route')) {
            return false;
        }

        $path = parse_url($requested_url, PHP_URL_PATH);
        if ($path && preg_match('#^/_dev(/|$)#', $path)) {
            return false;
        }

        return $redirect_url;
    }

    public static function handleDevRoutes(): void
    {
        $dev_route = \get_query_var('dev_route');

        if (empty($dev_route)) {
            return;
        }

        \nocache_headers();

        switch ($dev_route) {
            case 'index':
                static::renderDevIndex();
                break;

            case 'components':
                static::renderComponentList();
                break;

            case 'component':
                $component = \sanitize_file_name(\get_query_var('dev_component'));
                static::renderComponentExample($component);
                break;

            case 'globals':
                static::renderGlobals();
                break;

            case 'utilities':
                static::renderUtilities();
                break;

            case 'content':
                static::renderContent();
                break;

            case 'all_components':
                static::renderAllComponents();
                break;
        }

        exit;
    }

    protected static function renderDevIndex(): void
    {
        static::devPageOpen('Dev Kit', 'index');
        echo static::getDevIndexContent();
        static::devPageClose();
    }

    protected static function renderComponentList(): void
    {
        static::devPageOpen('Components', 'components');
        echo static::getComponentListContent();
        static::devPageClose();
    }

    protected static function renderComponentExample(string $component): void
    {
        $example_path = \get_theme_file_path("components/{$component}/example.php");

        if (! file_exists($example_path)) {
            \wp_die(
                \esc_html("No example found for component: {$component}"),
                'Component Not Found',
                ['response' => 404]
            );
        }

        $faker = class_exists('\Faker\Factory') ? \Faker\Factory::create() : null;

        ob_start();
        include $example_path;
        $example_output = ob_get_clean();

        $title = \esc_html(\ucwords(\str_replace('-', ' ', $component)));
        static::devPageOpen($title, 'component');

        echo '<div class="dev-component-example dev-flow">';
        echo '<div class="dev-page-header" data-dev-ui>';
        echo '<h1 class="dev-component-example__title">'.$title.'</h1>';
        echo '</div>';
        echo '<div class="dev-component-example__content flow">'.$example_output.'</div>';
        echo '</div>';

        static::devPageClose();
    }

    protected static function getDevIndexContent(): string
    {
        $content = '<div class="dev-index dev-flow" data-dev-ui>';
        $content .= '<div class="dev-page-header">';
        $content .= '<h1 class="dev-page-title" data-dev-ui>Dev Kit</h1>';
        $content .= '</div>';
        $content .= '<ul class="dev-link-list">';
        $content .= '<li><a href="'.\esc_url(\home_url('/_dev/globals/')).'">Globals</a></li>';
        $content .= '<li><a href="'.\esc_url(\home_url('/_dev/utilities/')).'">Utilities</a></li>';
        $content .= '<li><a href="'.\esc_url(\home_url('/_dev/components/')).'">Components</a></li>';
        $content .= '<li><a href="'.\esc_url(\home_url('/_dev/content/')).'">Content</a></li>';
        $content .= '</ul>';
        $content .= '</div>';

        return $content;
    }

    protected static function getComponentListContent(): string
    {
        $components = static::getComponentsWithExamples();

        $content = '<div class="dev-component-list dev-flow" data-dev-ui>';
        $content .= '<div class="dev-page-header">';
        $content .= '<h1 class="dev-page-title" data-dev-ui>Components</h1>';
        $content .= '</div>';

        if (empty($components)) {
            $content .= '<p data-dev-ui>No components with examples found. Add an <code>example.php</code> file to a component directory.</p>';
        } else {
            $content .= '<ul class="dev-link-list">';
            $all_url = \esc_url(\home_url('/_dev/all-components/'));
            $content .= "<li><a href=\"{$all_url}\"><strong>All</strong></a></li>";
            foreach ($components as $comp_name) {
                $url = \esc_url(\home_url("/_dev/components/{$comp_name}/"));
                $label = \esc_html(\ucwords(\str_replace('-', ' ', $comp_name)));
                $content .= "<li><a href=\"{$url}\">{$label}</a></li>";
            }
            $content .= '</ul>';
        }

        $content .= '</div>';

        return $content;
    }

    protected static function renderGlobals(): void
    {
        $colors = static::getThemeColors();

        static::devPageOpen('Globals', 'globals');

        echo '<div class="dev-page-header" data-dev-ui>';
        echo '<h1 class="dev-page-title" data-dev-ui>Globals</h1>';
        echo '</div>';

        $template_path = static::getTemplatePath('globals.php');
        if ($template_path) {
            include $template_path;
        }

        $template_path = static::getTemplatePath('html-elements.php');
        if ($template_path) {
            include $template_path;
        }

        static::devPageClose();
    }

    protected static function renderContent(): void
    {
        static::devPageOpen('Content', 'content');

        echo '<div class="dev-page-header" data-dev-ui>';
        echo '<h1 class="dev-page-title" data-dev-ui>Content</h1>';
        echo '</div>';

        $template_path = static::getTemplatePath('content.php');
        if ($template_path) {
            include $template_path;
        }

        static::devPageClose();
    }

    protected static function renderUtilities(): void
    {
        static::devPageOpen('Utilities', 'utilities');

        echo '<div class="dev-page-header" data-dev-ui>';
        echo '<h1 class="dev-page-title" data-dev-ui>Utilities</h1>';
        echo '</div>';

        $template_path = static::getTemplatePath('utilities.php');
        if ($template_path) {
            include $template_path;
        }

        static::devPageClose();
    }

    protected static function renderAllComponents(): void
    {
        $components = static::getComponentsWithExamples();
        $faker = class_exists('\Faker\Factory') ? \Faker\Factory::create() : null;

        static::devPageOpen('All Components', 'all-components');

        echo '<div class="dev-page-header" data-dev-ui>';
        echo '<h1 class="dev-page-title" data-dev-ui>All Components</h1>';
        echo '</div>';

        if (empty($components)) {
            echo '<p data-dev-ui>No components with examples found. Add an <code>example.php</code> file to a component directory.</p>';
        } else {
            foreach ($components as $component) {
                $example_path = \get_theme_file_path("components/{$component}/example.php");

                if (file_exists($example_path)) {
                    echo '<div class="dev-kit__section" id="component-'.\esc_attr($component).'">';
                    echo '<h2 data-dev-ui>'.\esc_html(\ucwords(\str_replace('-', ' ', $component))).'</h2>';

                    ob_start();
                    include $example_path;
                    $example_output = ob_get_clean();

                    echo '<div class="component-example-section__content flow">'.$example_output.'</div>';
                    echo '</div>';
                }
            }
        }

        static::devPageClose();
    }

    /**
     * Get template path with theme override support.
     * Checks Theme/Dev/templates/ first, falls back to Gust/Dev/templates/.
     */
    protected static function getTemplatePath(string $template): ?string
    {
        $theme_path = \get_theme_file_path("Theme/Dev/templates/{$template}");
        if (file_exists($theme_path)) {
            return $theme_path;
        }

        $framework_path = \get_theme_file_path("Gust/Dev/templates/{$template}");
        if (file_exists($framework_path)) {
            return $framework_path;
        }

        return null;
    }

    protected static function getComponentsWithExamples(): array
    {
        $examples = glob(\get_theme_file_path('components/*/example.php'));

        return array_map(function ($path) {
            return basename(dirname($path));
        }, $examples ?: []);
    }

    protected static function getThemeColors(): array
    {
        return \Gust\WordPress\Colors::getWordPressPalette();
    }

    public static function flushRules(): void
    {
        static::addRewriteRules();
        \flush_rewrite_rules();
    }
}
