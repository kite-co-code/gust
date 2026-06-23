<?php
/**
 * Promo Template
 *
 * @var \Gust\Components\Promo $this
 */
?>

<section class="<?= classes('promo', $this->classes) ?>" <?= attributes($this->attributes); ?>>
    <div class="promo__inner">
        <h2 class="promo__title"><?= esc_html($this->title); ?></h2>

        <?php if ($this->subheading): ?>
            <p class="promo__subheading"><?= esc_html($this->subheading); ?></p>
        <?php endif; ?>

        <?php if ($this->link): ?>
            <a class="promo__button btn"
               href="<?= esc_url($this->link['url']); ?>"
               <?= $this->link['target'] ? 'target="_blank" rel="noopener"' : ''; ?>>
                <?= esc_html($this->link['title']); ?>
            </a>
        <?php endif; ?>
    </div>
</section>
