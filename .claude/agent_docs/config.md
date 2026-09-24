# config.json

Runtime settings for the Gust framework. Loaded by `Gust\Config::init()` from `config.json` at the theme root. Access values via `\Gust\Config::get('key', $default)`.

Any key can be overridden at runtime via filter:
```php
add_filter('gust/config/site_guide', fn() => true);
```

---

## Admin

**`site_guide`** — bool, default `false`
Enable the Site Guide admin page and dashboard widget. When disabled, the menu item is also excluded from the admin menu order.

---

## Editor

**`editor.allowed_blocks`** — string[], default see config.json
Explicit allowlist of block names shown in the block inserter.

**`editor.disabled_blocks`** — string[], default see config.json
Blocks to unregister. Use `"..."` as a placeholder sentinel (ignored).

---

## Content

**`deactivate_comments`** — bool, default `false`
Remove comment support from all post types.

**`deactivate_posts_post_type`** — bool, default `false`
Hide the built-in Posts post type from the admin.

---

## Styles

**`remove_wp_global_styles`** — bool, default `false`
Dequeue WP global styles (`global-styles` inline style).

**`remove_wp_block_library_styles`** — bool, default `false`
Dequeue block library CSS (`wp-block-library`).

---

## Scripts

**`ajax_required`** — bool, default `false`
Keep `wp-ajax-url` localized script in front-end output.

**`jquery_required`** — bool, default `false`
Keep jQuery enqueued on the front end.

**`jquery_in_footer`** — bool, default `false`
Move jQuery to the footer.

**`remove_jquery_migrate`** — bool, default `false`
Dequeue `jquery-migrate`.

---

## PWA

**`enable_webmanifest`** — bool, default `false`
Output a `<link rel="manifest">` tag pointing to `/site.webmanifest`.

---

## Images

**`image_sizes`** — object
Register or disable image sizes. Value `[w, h, crop]` to add; `false` to remove a WP default.

**`jpeg_upload_quality`** — int, default `90`
JPEG compression quality for uploaded images.

**`max_image_size`** — int
Max pixel dimension (width or height) for uploaded images.
