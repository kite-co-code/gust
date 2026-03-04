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
        echo <<<'HTML'
            <style>
                /* ============================================
                   Gust Dev Tools — hardcoded design tokens
                   (intentionally isolated from project styles)
                   ============================================ */
                *, *::before, *::after { box-sizing: border-box; }

                :root {
                    --dev-mono:      ui-monospace, 'Cascadia Code', 'Source Code Pro', Menlo, Monaco, Consolas, monospace;
                    --dev-sans:      system-ui, -apple-system, sans-serif;
                    --dev-text:      #1a1a2a;
                    --dev-muted:     #6b6b7b;
                    --dev-border:    #d0d0da;
                    --dev-bg:        #f2f2f7;
                    --dev-surface:   #ffffff;
                    --dev-accent:    #0707a3;
                    --dev-accent-bg: #ebebf8;
                    --dev-accent-hover: #0505cc;
                    --dev-ink:       #111118;
                    --dev-ink-fg:    #ebebf8;
                }

                body {
                    margin: 0;
                    font-family: var(--dev-sans);
                    font-size: 1rem;
                    color: var(--dev-text);
                    background: var(--dev-bg);
                    min-height: 100vh;
                }

                /* ---- Top bar ---- */
                .dev-topbar {
                    display: flex;
                    align-items: center;
                    padding: 0 2rem;
                    height: 2.75rem;
                    background: var(--dev-accent);
                    border-bottom: 1px solid rgba(0,0,0,0.12);
                    position: sticky;
                    top: var(--wp-admin--admin-bar--height, 0px);
                    z-index: 100;
                }

                .dev-breadcrumb {
                    display: inline-flex;
                    align-items: center;
                    gap: 0.375rem;
                    font-size: 0.8125rem;
                    font-weight: 500;
                    color: rgba(255,255,255,0.6);
                }

                .dev-breadcrumb a {
                    color: rgba(255,255,255,0.85);
                    text-decoration: none;
                    transition: color 0.15s;
                }

                .dev-breadcrumb a:hover {
                    color: #fff;
                    text-decoration: none;
                }

                .dev-breadcrumb__sep {
                    opacity: 0.85;
                    font-size: 0.75rem;
                }

                .dev-breadcrumb__current {
                    color: rgba(255,255,255,0.95);
                }

                .dev-topbar__nav {
                    display: flex;
                    align-items: center;
                    gap: 0.125rem;
                    margin-left: auto;
                }

                .dev-topbar__nav a {
                    display: inline-flex;
                    align-items: center;
                    padding: 0.25rem 0.625rem;
                    font-size: 0.8125rem;
                    font-weight: 500;
                    color: rgba(255,255,255,0.75);
                    text-decoration: none;
                    border-radius: 4px;
                    transition: background 0.15s, color 0.15s;
                }

                .dev-topbar__nav a:hover,
                .dev-topbar__nav a[aria-current] {
                    background: rgba(255,255,255,0.15);
                    color: #fff;
                    text-decoration: none;
                }

                /* ---- Main content — hardcoded content grid (intentionally isolated from project content-grid) ---- */
                .dev-main {
                    --dev-grid-max: 960px;
                    --dev-grid-padding: 2rem;

                    display: grid;
                    grid-template-columns:
                        [full-start] minmax(var(--dev-grid-padding), 1fr)
                        [wide-start] minmax(0, var(--dev-grid-max))
                        [wide-end] minmax(var(--dev-grid-padding), 1fr)
                        [full-end];
                    padding-top: 2.5rem;
                    padding-bottom: 4rem;

                    > * {
                        grid-column: wide;
                    }

                    > .alignwide {
                        grid-column: wide;
                        width: 100%;
                        max-width: 100%;
                    }

                    > .alignfull {
                        grid-column: full;
                        width: 100%;
                        max-width: 100%;
                    }
                }

                /* ---- Dev chrome element resets (scoped to [data-dev-ui] only) ---- */
                [data-dev-ui] h1, h1[data-dev-ui],
                [data-dev-ui] h2, h2[data-dev-ui],
                [data-dev-ui] h3, h3[data-dev-ui],
                [data-dev-ui] h4, h4[data-dev-ui],
                [data-dev-ui] h5, h5[data-dev-ui],
                [data-dev-ui] h6, h6[data-dev-ui] {
                    font-family: var(--dev-sans) !important;
                    font-weight: 600 !important;
                    line-height: 1.3 !important;
                    letter-spacing: 0 !important;
                    text-transform: none !important;
                    color: var(--dev-text) !important;
                }

                [data-dev-ui] h2, h2[data-dev-ui] { font-size: 1.375rem !important; margin: 2rem 0 0.75rem !important; }
                [data-dev-ui] h3, h3[data-dev-ui] { font-size: 1.0625rem !important; margin: 1.5rem 0 0.5rem !important; }
                [data-dev-ui] h4, h4[data-dev-ui] { font-size: 0.9375rem !important; margin: 1.25rem 0 0.5rem !important; }

                [data-dev-ui] p, p[data-dev-ui] {
                    font-size: 0.9375rem;
                    line-height: 1.6;
                    color: var(--dev-text);
                    margin: 0 0 1rem;
                }

                /* ---- Page header ---- */
                .dev-page-header {
                    display: flex;
                    flex-direction: column;
                    gap: 0.5rem;
                    padding-bottom: 1.25rem;
                    margin-bottom: 2rem;
                    border-bottom: 1px solid var(--dev-border);
                }

                /* ---- Page title ---- */
                .dev-page-title {
                    font-size: 20px !important;
                    font-weight: 600 !important;
                    text-transform: uppercase !important;
                    letter-spacing: 0.08em !important;
                    color: var(--dev-muted) !important;
                    margin: 0 !important;
                    line-height: 1.3 !important;
                }

                /* ---- Back links ---- */
                .dev-back {
                    display: inline-flex;
                    align-items: center;
                    gap: 0.3rem;
                    font-size: 0.8125rem;
                    color: var(--dev-muted);
                    text-decoration: none;
                }

                .dev-back:hover {
                    color: var(--dev-accent);
                    text-decoration: none;
                }

                /* ---- Index nav cards ---- */
                .dev-index__links {
                    list-style: none;
                    padding: 0;
                    margin: 0;
                    display: grid;
                    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
                    gap: 0.75rem;
                }

                .dev-index__links a {
                    display: flex;
                    flex-direction: column;
                    gap: 0.25rem;
                    padding: 1rem 1.25rem;
                    background: var(--dev-surface);
                    color: var(--dev-accent);
                    text-decoration: none;
                    border-radius: 6px;
                    font-size: 0.875rem;
                    font-weight: 600;
                    border: 1px solid var(--dev-border);
                    border-left: 3px solid var(--dev-accent);
                    transition: border-color 0.15s, box-shadow 0.15s, transform 0.1s;
                }

                .dev-index__links a:hover {
                    border-color: var(--dev-accent-hover);
                    box-shadow: 0 2px 8px rgba(7,7,163,0.12);
                    transform: translateY(-1px);
                    color: var(--dev-accent-hover);
                    text-decoration: none;
                }

                /* ---- Component list ---- */
                .dev-link-list {
                    list-style: none;
                    padding: 0;
                    margin: 0;
                    display: flex;
                    flex-wrap: wrap;
                    gap: 0.4rem;
                }

                .dev-link-list a {
                    display: inline-block;
                    padding: 0.375rem 0.875rem;
                    background: var(--dev-surface);
                    color: var(--dev-accent);
                    text-decoration: none;
                    border-radius: 4px;
                    font-size: 0.8125rem;
                    border: 1px solid var(--dev-accent-bg);
                    transition: background 0.15s, border-color 0.15s;
                }

                .dev-link-list a:hover {
                    background: var(--dev-accent-bg);
                    border-color: var(--dev-accent);
                    text-decoration: none;
                }

                /* ---- Component example ---- */
                .dev-component-example__title {
                    font-size: 1rem !important;
                    font-weight: 600 !important;
                    margin: 0 !important;
                }

                .component-example-section {
                    margin-bottom: 2rem;
                }

                .component-example-section__title {
                    font-size: 0.6875rem;
                    font-weight: 600;
                    text-transform: uppercase;
                    letter-spacing: 0.08em;
                    color: var(--dev-muted);
                    margin-bottom: 0.5rem;
                    padding-bottom: 0.375rem;
                    border-bottom: 1px solid var(--dev-border);
                }

                .component-example-section__description {
                    font-size: 0.875rem;
                    color: var(--dev-muted);
                    margin-bottom: 1rem;
                }

                .component-example-section__preview {
                    padding: 1.5rem;
                    background: var(--dev-bg);
                    border: 1px solid var(--dev-border);
                    border-radius: 6px;
                }

                /* ---- Dev Kit section/subsection layout ---- */
                .dev-kit__section {
                    --flow-space: 2rem;
                    max-width: 100%;
                    padding: var(--space-base, 2rem) 0;
                }

                .dev-kit__section + .dev-kit__section {
                    border-top: 1px solid var(--dev-border);
                    margin-top: 0;
                }

                .dev-kit__section h2 {
                    font-family: var(--dev-sans) !important;
                    font-size: 0.6875rem !important;
                    font-weight: 600 !important;
                    text-transform: uppercase !important;
                    letter-spacing: 0.08em !important;
                    color: var(--dev-muted) !important;
                    margin-bottom: 1.5rem !important;
                    padding-bottom: 0.5rem;
                    border-bottom: 2px solid var(--dev-text);
                }

                .dev-kit__section h3 {
                    font-family: var(--dev-sans) !important;
                    font-size: 0.75rem !important;
                    font-weight: 600 !important;
                    text-transform: uppercase !important;
                    letter-spacing: 0.06em !important;
                    color: var(--dev-text) !important;
                    margin-top: 1.25rem !important;
                    margin-bottom: 0.4rem !important;
                }

                .dev-kit__subsection {
                    margin-top: 1.5rem;
                }

                .dev-kit__subsection > small {
                    display: block;
                    font-size: 0.8125rem;
                    color: var(--dev-muted);
                    margin-bottom: 0.5rem;
                    line-height: 1.5;
                }

                .dev-kit__demo {
                    padding: 1rem;
                    border: 1px dashed var(--dev-border);
                    border-radius: 6px;
                    margin-top: 0.5rem;
                    background: #ffffff;
                }

                .dev-kit__demo--dark {
                    background: var(--color-foreground);
                    color: var(--color-background);
                }

                .dev-kit__code {
                    display: inline-block;
                    padding: 0.15em 0.4em;
                    border-radius: 3px;
                    background: var(--dev-accent-bg);
                    color: var(--dev-accent);
                    font-family: var(--dev-mono);
                    font-size: 0.8125em;
                    font-weight: 400;
                }

                .dev-kit__box {
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    min-height: 60px;
                    padding: 0.5rem;
                    border: 1px solid var(--dev-border);
                    background: var(--dev-accent-bg);
                    text-align: center;
                }

                .dev-kit__box--tall {
                    min-height: 150px;
                }

                .dev-kit__placeholder {
                    display: block;
                    width: 100%;
                    height: 100%;
                    min-height: 80px;
                    background: linear-gradient(135deg, var(--dev-accent-bg) 25%, var(--dev-accent) 100%);
                }

                /* ---- Inline code ---- */
                code {
                    font-family: var(--dev-mono) !important;
                    font-size: 0.8125em;
                    background: var(--dev-accent-bg);
                    color: var(--dev-accent);
                    padding: 0.15em 0.4em;
                    border-radius: 3px;
                    font-weight: 400;
                }

                /* ---- Code blocks ---- */
                pre {
                    font-family: var(--dev-mono) !important;
                    background: var(--dev-ink);
                    color: var(--dev-ink-fg);
                    padding: 1.25rem 1.5rem;
                    border-radius: 6px;
                    overflow-x: auto;
                    font-size: 0.875rem;
                    line-height: 1.7;
                    tab-size: 2;
                    border: none;
                    margin: 0;
                }

                pre code {
                    background: none !important;
                    color: inherit !important;
                    padding: 0 !important;
                    border-radius: 0 !important;
                }
            </style>
        </head>
        <body>
        HTML;
        $nav_items = [
            'globals' => 'Globals',
            'utilities' => 'Utilities',
            'components' => 'Components',
            'content' => 'Content',
        ];
        $nav_html = '<nav class="dev-topbar__nav">';
        foreach ($nav_items as $route => $label) {
            $url = \esc_url(\home_url("/_dev/{$route}/"));
            $current = ($active_route === $route || ($route === 'components' && $active_route === 'component')) ? ' aria-current="page"' : '';
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

            if ($active_route === 'component') {
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
        echo '<div class="dev-main flow">';
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

        echo '<div class="dev-component-example">';
        echo '<div class="dev-page-header" data-dev-ui>';
        echo '<h1 class="dev-component-example__title">'.$title.'</h1>';
        echo '</div>';
        echo '<div class="dev-component-example__content">'.$example_output.'</div>';
        echo '</div>';

        static::devPageClose();
    }

    protected static function getDevIndexContent(): string
    {
        $content = '<div class="dev-index" data-dev-ui>';
        $content .= '<div class="dev-page-header">';
        $content .= '<h1 class="dev-page-title">Dev Kit</h1>';
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

        $content = '<div class="dev-component-list" data-dev-ui>';
        $content .= '<div class="dev-page-header">';
        $content .= '<h1 class="dev-page-title">Components</h1>';
        $content .= '</div>';

        if (empty($components)) {
            $content .= '<p>No components with examples found. Add an <code>example.php</code> file to a component directory.</p>';
        } else {
            $content .= '<ul class="dev-link-list flex-list">';
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
        echo '<h1 class="dev-page-title">Globals</h1>';
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
        echo '<h1 class="dev-page-title">Content</h1>';
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
        echo '<h1 class="dev-page-title">Utilities</h1>';
        echo '</div>';

        $template_path = static::getTemplatePath('utilities.php');
        if ($template_path) {
            include $template_path;
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
