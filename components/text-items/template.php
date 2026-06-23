<?php
/**
 * TextItems Template
 *
 * @var \Gust\Components\TextItems $this
 */
?>

<section class="<?= classes('text-items', 'wp-block', $this->classes) ?>" <?= attributes($this->attributes) ?>>
    <div class="text-items__inner content-width-lg align-left">
        <?php if (! empty($this->heading)) { ?>
            <?= \Gust\Components\Heading::make(
                heading: $this->heading,
                classes: ['text-items__heading'],
            ); ?>
        <?php } ?>

        <div class="text-items__list content-width-sm align-left">
            <?php foreach ($this->items as $item) { ?>
                <article class="text-items__item">
                    <div class="text-items__item-content">
                        <?php if (! empty($item['meta'])) { ?>
                            <div class="text-items__meta"><?= esc_html($item['meta']); ?></div>
                        <?php } ?>

                        <?php if (! empty($item['title'])) { ?>
                            <?= \Gust\Components\Heading::make(
                                heading: $item['title'],
                                el: 'h3',
                                classes: ['text-items__title'],
                            ); ?>
                        <?php } ?>

                        <?php if (! empty($item['description'])) { ?>
                            <div class="text-items__description"><?= wp_kses_post($item['description']); ?></div>
                        <?php } ?>
                    </div>
                </article>
            <?php } ?>
        </div>
    </div>
</section>
