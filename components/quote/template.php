<?php
/**
 * Quote Template
 *
 * @var \Gust\Components\Quote $this
 */

use Gust\SVG;

?>

<figure class="<?= classes('quote', 'wp-block', $this->classes) ?>" <?= attributes($this->attributes); ?>>
    <div class="quote__mark" aria-hidden="true">
        <?= SVG::get(get_theme_file_path('public/build/images/icons/quote.svg'), ['asset' => false]); ?>
    </div>

    <blockquote class="quote__content content-width-sm">
        <p class="quote__text"><?= nl2br(esc_html($this->quote)); ?></p>
    </blockquote>

    <?php if (! empty($this->credit) || ! empty($this->role)) { ?>
        <figcaption class="quote__citation">
            <?php if (! empty($this->credit)) { ?>
                <span class="quote__credit"><?= esc_html($this->credit); ?></span>
            <?php } ?>

            <?php if (! empty($this->role)) { ?>
                <span class="quote__role"><?= esc_html($this->role); ?></span>
            <?php } ?>
        </figcaption>
    <?php } ?>
</figure>
