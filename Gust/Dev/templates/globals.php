<?php
/**
 * Globals style guide template.
 *
 * @var array $colors Theme colors from WordPress palette
 */
?>
<section class="dev-kit__section flow">
    <h2 data-dev-ui>Design Tokens</h2>

    <?php if (! empty($colors)) : ?>

        <!-- Colors -->
        <div class="dev-kit__subsection">
            <h3>Colors</h3>
            <small><code class="dev-kit__code">--color-{name}</code></small>
            <div class="dev-kit__demo">
                <div style="display: flex; flex-wrap: wrap; gap: 1rem;">
                    <?php foreach ($colors as $color) : ?>
                        <div style="text-align: center;">
                            <div style="width: 80px; height: 80px; background: <?= esc_attr($color['color']); ?>; border: 1px solid #ccc; border-radius: 4px;"></div>
                            <small><?= esc_html($color['name'] ?? $color['slug']); ?></small><br>
                            <code class="dev-kit__code" style="font-size: 0.75rem;"><?= esc_html($color['color']); ?></code>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Color Contexts -->
        <div class="dev-kit__subsection">
            <h3>Color Contexts</h3>
            <small>
                <code class="dev-kit__code">.color-context-{color}</code> — sets background, foreground, link, and focus colors together.
            </small>
            <div class="dev-kit__demo" style="padding: 0; overflow: hidden; border-radius: 6px;">
                <?php foreach ($colors as $color) : ?>
                    <div class="color-context-<?= esc_attr($color['slug']); ?>" style="padding: 1.5rem;">
                        <strong><?= esc_html($color['name'] ?? $color['slug']); ?></strong>
                        <code class="dev-kit__code" style="opacity: 0.7;">.color-context-<?= esc_attr($color['slug']); ?></code>
                        <p style="margin: 0.5rem 0 0;">Sample text with <a href="#">a link</a> to demonstrate foreground and link colors.</p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    <?php else : ?>
        <p data-dev-ui>No theme colors defined in theme.json</p>
    <?php endif; ?>
</section>
