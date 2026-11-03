<?php

namespace Gust\WordPress;

/**
 * Adds, removes, or changes WP functionality to improve site security.
 */
class Security
{
    public static function init(): void
    {
        // Hide current WP version globally to give less information to attackers.
        \add_action('init', [__CLASS__, 'hideWPVersion']);

        // Filter login errors to give less information to attackers.
        \add_filter('login_errors', [__CLASS__, 'filterLoginErrorMessages']);

        // Hide 'Users' REST API endpoints from users who can't list users or edit posts.
        \add_filter('rest_request_before_callbacks', [__CLASS__, 'filterRestAPIUsersEndpoint'], 10, 3);

        // Stop ?author=<id> requests revealing usernames to logged-out visitors.
        \add_filter('request', [__CLASS__, 'blockAuthorEnumeration']);

        // Remove the users sitemap from core's sitemaps.
        \add_filter('wp_sitemaps_add_provider', [__CLASS__, 'removeUsersSitemapProvider'], 10, 2);
    }

    /**
     * Filter generated WP version output from various feeds and locations.
     *
     * @see /wp-includes/default-filters.php
     * @see /wp-includes/general-template.php
     */
    public static function hideWPVersion(): void
    {
        \add_action('the_generator', '__return_empty_string');
    }

    /**
     * Filter login error messages so that username enumeration cannot happen via the login form.
     *
     * @see /wp-login.php
     *
     * @param string The error message.
     * @return string The filtered error message.
     */
    public static function filterLoginErrorMessages(string $error): string
    {
        return \__('Your username or password is incorrect', 'gust');
    }

    /**
     * Filters the REST API response before executing any callbacks to hide the 'Users' endpoints
     * (`/wp/v2/users`, `/wp/v2/users/<id>`, `/wp/v2/users/me` etc.) from users who can't list
     * users or edit posts.
     *
     * Note that this filter will not be called for requests that fail to authenticate or
     * fail to match a registered route.
     *
     * @link: https://developer.wordpress.org/reference/hooks/rest_request_before_callbacks/
     *
     * @param  WP_REST_Response|WP_HTTP_Response|WP_Error|mixed  $response  Result to send to the client.
     *                                                                      Usually a WP_REST_Response or WP_Error.
     * @param  array  $handler  Route handler used for the request.
     * @param  WP_REST_Request  $request  Request used to generate the response.
     * @return WP_REST_Response|WP_HTTP_Response|WP_Error|mixed The filtered request used to generate the response.
     */
    public static function filterRestAPIUsersEndpoint($response, $handler, $request)
    {
        if (\current_user_can('list_users') || \current_user_can('edit_posts')) {
            return $response;
        }

        if (static::isUsersRestRequest($handler, $request)) {
            return new \WP_Error(
                'forbidden',
                \__('Access forbidden.', 'gust'),
                [
                    'status' => 403,
                ]
            );
        }

        return $response;
    }

    /**
     * Whether a REST request is for one of the 'Users' endpoints.
     *
     * Checks the handler's controller as well as the route, because WP matches routes
     * case-insensitively (`/wp/v2/Users` reaches the same handler).
     *
     * @param  array  $handler  Route handler used for the request.
     * @param  WP_REST_Request  $request  Request used to generate the response.
     */
    protected static function isUsersRestRequest($handler, $request): bool
    {
        $callback = $handler['callback'] ?? null;

        if (is_array($callback) && ($callback[0] ?? null) instanceof \WP_REST_Users_Controller) {
            return true;
        }

        return (bool) preg_match('#^/wp/v2/users(/|$)#i', (string) $request->get_route());
    }

    /**
     * Stop logged-out visitors finding usernames with `?author=<id>`, which core redirects to
     * the author's archive (`/author/<username>/`). The request 404s instead.
     *
     * @param  array  $query_vars  The parsed query variables.
     * @return array The filtered query variables.
     */
    public static function blockAuthorEnumeration(array $query_vars): array
    {
        if (\is_admin() || \is_user_logged_in()) {
            return $query_vars;
        }

        if (isset($_GET['author']) || isset($query_vars['author'])) {
            unset($query_vars['author']);
            $query_vars['error'] = '404';
        }

        return $query_vars;
    }

    /**
     * Remove the users provider from core's sitemaps, so usernames aren't listed in wp-sitemap-users-1.xml.
     *
     * @param  \WP_Sitemaps_Provider|false  $provider  The sitemap provider.
     * @param  string  $name  The provider's name.
     * @return \WP_Sitemaps_Provider|false The provider, or false to remove it.
     */
    public static function removeUsersSitemapProvider($provider, string $name)
    {
        return $name === 'users' ? false : $provider;
    }
}
