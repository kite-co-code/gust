<?php

namespace Gust\Components;

use Gust\Component;
use Gust\ComponentBase;

/**
 * VideoItem Component
 *
 * Usage:
 *   use Gust\Components\VideoItem;
 *
 *   echo VideoItem::make();
 */
class VideoItem extends ComponentBase
{
    protected static string $name = 'video-item';

    /**
     * Create a new VideoItem component.
     *
     * @return static|null Returns null if component should not render.
     */
    public static function make(
        array $classes = [],
        string $heading = '',
        string $content = '',
        string $video = '',
        ...$others
    ): ?static {
        return static::createFromArgs(static::mergeArgs(get_defined_vars()));
    }

    /**
     * The HTML allowed in the video embed: a single iframe.
     */
    public static function allowedEmbedHtml(): array
    {
        return [
            'iframe' => [
                'src' => true,
                'data-src' => true,
                'title' => true,
                'width' => true,
                'height' => true,
                'frameborder' => true,
                'allow' => true,
                'allowfullscreen' => true,
                'referrerpolicy' => true,
                'loading' => true,
            ],
        ];
    }

    /**
     * Don't render without an iframe embed, e.g. when the oEmbed lookup failed.
     */
    protected static function validate(array $args): bool
    {
        return static::getEmbedSrc($args['video'] ?? '') !== null;
    }

    /**
     * Transform args before rendering.
     */
    protected static function transform(array $args): array
    {
        if (! empty($args['image'])) {
            $args['image']['size'] = 'large';
        }

        $video = \wp_kses($args['video'], static::allowedEmbedHtml());

        // Add extra parameters to src.
        $src = \add_query_arg([
            'autoplay' => 1,
            'modestbranding' => 1,
            'rel' => 0, // Whether to show videos from the same channel upon completion.
        ], static::getEmbedSrc($video));

        // Move the src to data-src, so the video only loads (and autoplays) when opened.
        $video = preg_replace(
            '/\ssrc="[^"]*"/',
            ' data-src="'.\esc_url($src, ['http', 'https']).'"',
            $video,
            1
        );

        // Iframes need an accessible name.
        if (! preg_match('/<iframe\s[^>]*\btitle="/i', $video)) {
            $title = $args['heading'] ?: \__('Video', 'gust');
            $video = preg_replace('/<iframe\b/i', '<iframe title="'.\esc_attr(\wp_strip_all_tags($title)).'"', $video, 1);
        }

        $args['video'] = $video;

        return $args;
    }

    /**
     * Get the iframe src from the embed HTML, or null if there isn't a valid one.
     */
    protected static function getEmbedSrc(string $html): ?string
    {
        $html = \wp_kses($html, static::allowedEmbedHtml());

        if (! preg_match('/<iframe\s[^>]*\bsrc="([^"]+)"/i', $html, $matches)) {
            return null;
        }

        $src = html_entity_decode($matches[1], ENT_QUOTES);

        // Only absolute http(s) or protocol-relative URLs.
        return preg_match('#^(https?:)?//[^/\s]#i', $src) ? $src : null;
    }
}
