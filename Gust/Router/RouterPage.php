<?php

namespace Gust\Router;

use Gust\Router;

/**
 * Router pages: published pages that hold the editable content for a route
 * (title, blocks, SEO). They aren't meant to be visited on their own, so a
 * direct visit redirects (301) to the route's URL, or is a 404 when the route
 * has no URL of its own (search, 404). They're left out of sitemaps and
 * marked noindex wherever they still render (e.g. previews).
 *
 * The page ID for each role is stored in the autoloaded `gust_router_page_{role}`
 * option. Pages are checked and created only when the registered roles change
 * (hash in `gust_router_pages_version`) or a router page is trashed or deleted.
 * Delete that option to force a re-check.
 */
class RouterPage
{
    const VERSION_OPTION = 'gust_router_pages_version';

    protected static array $pages = [];

    public static function init(): void
    {
        \add_action('init', [static::class, 'ensureAllPages'], 20);
        \add_action('trashed_post', [static::class, 'invalidate']);
        \add_action('before_delete_post', [static::class, 'invalidate'], 5);
        \add_action('before_delete_post', [static::class, 'preventDeletion']);
        \add_filter('wp_insert_post_data', [static::class, 'preventStructuralChanges'], 10, 2);
        \add_filter('page_link', [static::class, 'filterPageLink'], 10, 2);
        \add_filter('display_post_states', [static::class, 'addRoleBadge'], 10, 2);
        \add_filter('page_row_actions', [static::class, 'removeTrashAction'], 10, 2);

        // Direct visits, sitemaps and robots. Runs before Router::matchDecoratedRoutes (priority 1).
        \add_action('template_redirect', [static::class, 'handleDirectVisit'], 0);
        \add_filter('wp_sitemaps_posts_query_args', [static::class, 'excludeFromCoreSitemap'], 10, 2);
        \add_filter('wpseo_exclude_from_sitemap_by_post_ids', [static::class, 'excludeFromYoastSitemap']);
        \add_filter('wp_robots', [static::class, 'filterRobots']);
        \add_filter('wpseo_robots', [static::class, 'filterYoastRobots']);
    }

    public static function ensure(string $role, array $attributes = []): void
    {
        static::$pages[$role] = $attributes;
    }

    /**
     * Create any missing router pages. Skipped (no queries beyond autoloaded
     * options) while the registered roles match the stored hash.
     */
    public static function ensureAllPages(): void
    {
        $version = md5(serialize(static::$pages));

        if (\get_option(static::VERSION_OPTION) === $version) {
            return;
        }

        $complete = true;

        foreach (static::$pages as $role => $attributes) {
            $option = static::optionName($role);
            $pageId = (int) \get_option($option);
            $page = $pageId ? \get_post($pageId) : null;

            if (! $page || $page->post_status === 'trash') {
                $created = static::create($role, $attributes);
                $complete = $complete && $created > 0;
            } else {
                // Options saved before they were autoloaded.
                \wp_set_option_autoload($option, true);
            }
        }

        if ($complete) {
            \update_option(static::VERSION_OPTION, $version, true);
        }
    }

    /**
     * Re-check router pages on the next request when one is trashed or deleted,
     * including orphaned ones whose role may be registered again later.
     */
    public static function invalidate(int $postId): void
    {
        if (static::isRouterPage($postId)) {
            \delete_option(static::VERSION_OPTION);
        }
    }

    protected static function optionName(string $role): string
    {
        return "gust_router_page_{$role}";
    }

    public static function create(string $role, array $attributes = []): int
    {
        $title = $attributes['title'] ?? ucwords(str_replace('-', ' ', $role));
        $content = $attributes['content'] ?? '';

        $pageId = \wp_insert_post([
            'post_type' => 'page',
            'post_title' => $title,
            'post_name' => $role,
            'post_status' => 'publish',
            'post_content' => $content,
            'meta_input' => [
                '_is_router_page' => true,
                '_router_role' => $role,
            ],
        ]);

        if (\is_wp_error($pageId)) {
            return 0;
        }

        if ($pageId) {
            \update_option(static::optionName($role), $pageId, true);
        }

        return $pageId;
    }

    public static function isRouterPage(int $postId): bool
    {
        return (bool) \get_post_meta($postId, '_is_router_page', true);
    }

    public static function isActiveRouterPage(int $postId): bool
    {
        $role = static::getRole($postId);

        return $role !== null && isset(static::$pages[$role]);
    }

    public static function getRole(int $postId): ?string
    {
        $role = \get_post_meta($postId, '_router_role', true);

        return $role ?: null;
    }

    public static function getPageByRole(string $role): ?\WP_Post
    {
        $pageId = \get_option(static::optionName($role));

        if (! $pageId) {
            return null;
        }

        // Filter for multi-lingual support (e.g., Polylang).
        $pageId = \apply_filters('gust/router/page-id', $pageId, $role);

        $page = \get_post($pageId);

        return $page instanceof \WP_Post ? $page : null;
    }

    public static function preventDeletion(int $postId): void
    {
        if (static::isActiveRouterPage($postId)) {
            \wp_die(
                \__('Router pages cannot be deleted.', 'gust'),
                \__('Action Blocked', 'gust'),
                ['back_link' => true]
            );
        }
    }

    public static function preventStructuralChanges(array $data, array $postarr): array
    {
        if (empty($postarr['ID']) || ! static::isActiveRouterPage($postarr['ID'])) {
            return $data;
        }

        $original = \get_post($postarr['ID']);

        if (! $original) {
            return $data;
        }

        // Preserve slug and parent
        $data['post_name'] = $original->post_name;
        $data['post_parent'] = $original->post_parent;

        // Prevent status change to trash
        if ($data['post_status'] === 'trash') {
            $data['post_status'] = $original->post_status;
        }

        return $data;
    }

    public static function filterPageLink(string $link, int $postId): string
    {
        static $resolving = [];

        if (isset($resolving[$postId])) {
            return $link;
        }

        if (! static::isRouterPage($postId)) {
            return $link;
        }

        $role = static::getRole($postId);
        if (! $role) {
            return $link;
        }

        $route = Router::getRouteByRole($role);
        if ($route) {
            $resolving[$postId] = true;
            $url = $route->getUrl();
            unset($resolving[$postId]);

            return $url;
        }

        return $link;
    }

    public static function addRoleBadge(array $states, \WP_Post $post): array
    {
        if (! static::isRouterPage($post->ID)) {
            return $states;
        }

        $role = static::getRole($post->ID);
        $label = ucwords(str_replace('-', ' ', $role ?? ''));

        if (static::isActiveRouterPage($post->ID)) {
            $states['router_page'] = sprintf(
                /* translators: %s: Router page role name */
                \__('Role: %s', 'gust'),
                $label
            );
        } else {
            $states['router_page'] = sprintf(
                /* translators: %s: Router page role name */
                \__('Orphaned Route: %s', 'gust'),
                $label
            );
        }

        return $states;
    }

    public static function removeTrashAction(array $actions, \WP_Post $post): array
    {
        if (static::isActiveRouterPage($post->ID)) {
            unset($actions['trash']);
        }

        return $actions;
    }

    /**
     * A router page visited at its own permalink (e.g. /search/, /events/ or
     * ?page_id=) redirects to its route's URL, or becomes a 404 when the route
     * has none. Previews and a router page set as the front page still render.
     */
    public static function handleDirectVisit(): void
    {
        if (! \is_page() || \is_preview() || \is_front_page()) {
            return;
        }

        $page = \get_queried_object();

        if (! $page instanceof \WP_Post || ! static::isActiveRouterPage($page->ID)) {
            return;
        }

        $route = Router::getRouteByRole(static::getRole($page->ID));
        $url = $route?->getPublicUrl();

        if ($url) {
            $requestPath = \wp_parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '/';
            $routePath = \wp_parse_url($url, PHP_URL_PATH) ?: '/';

            // Already on the route's URL: nothing to redirect to.
            if (\untrailingslashit($requestPath) === \untrailingslashit($routePath)) {
                return;
            }

            \wp_safe_redirect($url, 301, 'Gust Router');
            exit;
        }

        global $wp_query;
        $wp_query->set_404();
        \status_header(404);
        \nocache_headers();

        // Otherwise redirect_canonical() sends the 404 on to the page's (filtered) permalink.
        \add_filter('redirect_canonical', '__return_false');
    }

    /**
     * IDs of published router pages for registered roles, including any
     * duplicates that carry the same role.
     *
     * @return int[]
     */
    public static function getActivePageIds(): array
    {
        if (! static::$pages) {
            return [];
        }

        return array_map('intval', \get_posts([
            'post_type' => 'page',
            'post_status' => 'any',
            'fields' => 'ids',
            'posts_per_page' => -1,
            'no_found_rows' => true,
            'meta_query' => [[
                'key' => '_router_role',
                'value' => array_keys(static::$pages),
                'compare' => 'IN',
            ]],
        ]));
    }

    public static function excludeFromCoreSitemap(array $args, string $postType): array
    {
        if ($postType !== 'page') {
            return $args;
        }

        $args['post__not_in'] = array_merge($args['post__not_in'] ?? [], static::getActivePageIds());

        return $args;
    }

    /**
     * @param  int[]  $ids
     * @return int[]
     */
    public static function excludeFromYoastSitemap($ids): array
    {
        return array_merge((array) $ids, static::getActivePageIds());
    }

    protected static function isViewingRouterPage(): bool
    {
        return \is_page() && static::isActiveRouterPage(\get_queried_object_id());
    }

    public static function filterRobots(array $robots): array
    {
        if (static::isViewingRouterPage()) {
            $robots['noindex'] = true;
        }

        return $robots;
    }

    /**
     * @param  string|array  $robots
     * @return string|array
     */
    public static function filterYoastRobots($robots)
    {
        if (! static::isViewingRouterPage()) {
            return $robots;
        }

        // Yoast passes a string like "index, follow"; keep the other directives.
        if (is_string($robots)) {
            $directives = array_filter(array_map('trim', explode(',', $robots)), fn ($d) => ! in_array($d, ['index', 'noindex'], true));

            return implode(', ', array_merge(['noindex'], $directives));
        }

        return $robots;
    }
}
