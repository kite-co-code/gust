<?php

/**
 * Plugin Name: Gust Worktree Switch (development only)
 * Description: Serves a git worktree of the active theme so agents can test a branch on the shared local site. Worktrees live in <theme>/.claude/worktrees/<name>. Browser: ?worktree=<name> sets a cookie, ?worktree=main clears it. WP-CLI: run from inside the worktree, or set GUST_WORKTREE=<name>. Only runs when wp-config.php sets WP_ENVIRONMENT_TYPE to development (pnpm env:setup). Symlinked into mu-plugins by dev-scripts/worktree-setup.sh. See .claude/agent_docs/worktrees.md.
 */
final class GustWorktreeSwitch
{
    private const QUERY = 'worktree';

    private const COOKIE = 'gust_worktree';

    private const ENV = 'GUST_WORKTREE';

    private static string $name = '';

    private static string $root = '';

    private static string $base = '';

    public static function boot(): void
    {
        if (! self::isDevelopment()) {
            return;
        }

        self::$base = get_option('stylesheet');
        self::$root = get_theme_root(self::$base).'/'.self::$base.'/.claude/worktrees';

        $name = self::requested();

        if ($name === null) {
            return;
        }

        if (! file_exists(self::$root.'/'.$name.'/style.css')) {
            self::forget();
            wp_die(
                sprintf(
                    'Worktree <code>%s</code> not found in <code>%s</code>. Check the name, or <a href="%s">go back to the main checkout</a>.',
                    esc_html($name),
                    esc_html(self::$root),
                    esc_url(add_query_arg(self::QUERY, 'main', home_url('/')))
                ),
                'Worktree not found',
                ['response' => 404]
            );
        }

        self::$name = $name;
        register_theme_directory(self::$root);

        add_filter('template', [__CLASS__, 'name']);
        add_filter('stylesheet', [__CLASS__, 'name']);
        // get_raw_theme_root() only reads the *_root options when they name the
        // current theme; otherwise it falls back to the cached theme_roots
        // transient, which has no worktrees, and the theme resolves to nothing.
        add_filter('pre_option_template', [__CLASS__, 'name']);
        add_filter('pre_option_stylesheet', [__CLASS__, 'name']);
        add_filter('pre_option_template_root', [__CLASS__, 'root']);
        add_filter('pre_option_stylesheet_root', [__CLASS__, 'root']);

        // Theme mods (menu locations, logo) are stored per stylesheet. Share the
        // base theme's, so a worktree behaves like the theme it was cut from.
        add_filter('pre_option_theme_mods_'.$name, fn () => get_option('theme_mods_'.self::$base) ?: []);
        add_filter('pre_update_option_theme_mods_'.$name, function ($value, $old) {
            update_option('theme_mods_'.self::$base, $value);

            return $old;
        }, 10, 2);

        // Rewrite rules are compiled from the post types, taxonomies and routes
        // the theme registers. Keep a copy per worktree, so a branch that adds a
        // post type doesn't leak its rules to main, or lose them on main's next flush.
        add_filter('pre_option_rewrite_rules', fn () => get_option(self::rewriteOption(), ''));
        add_filter('pre_update_option_rewrite_rules', function ($value, $old) {
            update_option(self::rewriteOption(), $value, false);

            return $old;
        }, 10, 2);

        if (! headers_sent() && ! self::isCli()) {
            header('X-Gust-Worktree: '.$name);
        }

        add_action('admin_bar_menu', [__CLASS__, 'adminBar'], 100);
        add_action('wp_footer', [__CLASS__, 'badge'], 100);
    }

    public static function name(): string
    {
        return self::$name;
    }

    public static function root(): string
    {
        return self::$root;
    }

    public static function rewriteOption(): string
    {
        return 'rewrite_rules_worktree_'.self::$name;
    }

    public static function adminBar(\WP_Admin_Bar $bar): void
    {
        $bar->add_node([
            'id' => 'gust-worktree',
            'title' => 'Worktree: '.esc_html(self::$name),
            'href' => add_query_arg(self::QUERY, 'main'),
            'meta' => ['title' => 'Serving worktree '.self::$name.'. Click to go back to the main checkout.'],
        ]);
    }

    public static function badge(): void
    {
        if (is_admin_bar_showing()) {
            return;
        }

        printf(
            '<div data-gust-worktree="%1$s" style="position:fixed;bottom:8px;left:8px;z-index:99999;font:12px/1 ui-monospace,monospace;background:#0e26ac;color:#fff;padding:6px 8px;border-radius:4px;opacity:.85" aria-hidden="true">worktree: %1$s</div>',
            esc_html(self::$name)
        );
    }

    /**
     * The worktree named by the request, or null for the main checkout.
     */
    private static function requested(): ?string
    {
        if (self::isCli()) {
            $value = getenv(self::ENV);

            return self::valid($value !== false ? $value : self::fromWorkingDirectory());
        }

        if (isset($_GET[self::QUERY])) {
            $value = self::valid($_GET[self::QUERY]);
            $value === null ? self::forget() : self::remember($value);

            return $value;
        }

        return self::valid($_COOKIE[self::COOKIE] ?? null);
    }

    /**
     * WP-CLI run from inside .claude/worktrees/<name> serves that worktree.
     */
    private static function fromWorkingDirectory(): ?string
    {
        $cwd = realpath(getcwd());
        $root = realpath(self::$root);

        if (! $cwd || ! $root || ! str_starts_with($cwd.'/', $root.'/')) {
            return null;
        }

        return explode('/', substr($cwd, strlen($root) + 1))[0] ?: null;
    }

    private static function valid(mixed $value): ?string
    {
        if (! is_string($value) || $value === '' || $value === 'main') {
            return null;
        }

        return preg_match('/^[A-Za-z0-9][A-Za-z0-9._-]*$/', $value) ? $value : null;
    }

    private static function remember(string $name): void
    {
        setcookie(self::COOKIE, $name, ['path' => '/', 'secure' => is_ssl(), 'httponly' => true, 'samesite' => 'Lax']);
    }

    private static function forget(): void
    {
        if (isset($_COOKIE[self::COOKIE]) && ! headers_sent()) {
            setcookie(self::COOKIE, '', ['expires' => time() - 3600, 'path' => '/', 'secure' => is_ssl(), 'httponly' => true, 'samesite' => 'Lax']);
        }
    }

    /**
     * WP_ENVIRONMENT_TYPE is defined in wp-config.php (pnpm env:setup), which
     * loads before mu-plugins, so wp_get_environment_type() is safe to call and
     * cache here.
     */
    private static function isDevelopment(): bool
    {
        return wp_get_environment_type() === 'development';
    }

    private static function isCli(): bool
    {
        return defined('WP_CLI') && WP_CLI;
    }
}

// After mu-plugins load, before the theme is resolved (setup_theme runs later).
add_action('plugins_loaded', [GustWorktreeSwitch::class, 'boot'], 0);
