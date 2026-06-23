<section id="trip-highlights" class="<?= classes('trip-highlights', 'wp-block', $this->classes) ?>" <?= attributes($this->attributes) ?>>
    <div class="trip-highlights__inner content-width-lg">
        <?= \Gust\Components\Heading::make(
            heading: __('Highlights', 'gust'),
            el: 'h4',
            classes: ['trip-highlights__heading'],
        ); ?>

        <div class="trip-highlights__items">
            <?php foreach ($this->items as $item) { ?>
                <article class="trip-highlights__item">
                    <?php if (! empty($item['image'])) { ?>
                        <div class="trip-highlights__item-image">
                            <div class="trip-highlights__item-image-inner img-fit">
                                <?= $item['image']; ?>
                            </div>
                        </div>
                    <?php } ?>
                    
                    <div class="trip-highlights__item-content">
                    <?php if (! empty($item['heading'])) { ?>
                        <?= \Gust\Components\Heading::make(
                            heading: $item['heading'],
                            el: 'h6',
                            classes: ['trip-highlights__item-heading'],
                        ); ?>
                    <?php } ?>

                    <?php if (! empty($item['description'])) { ?>
                        <p class="trip-highlights__item-description"><?= esc_html($item['description']); ?></p>
                    <?php } ?>
                    </div>
                </article>
            <?php } ?>
        </div>
    </div>
</section>
