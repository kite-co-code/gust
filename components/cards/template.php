<?php
$is_carousel = ($this->type ?? null) === 'carousel';
$tag = $is_carousel ? 'sq-cards' : 'section';
?>
<?php if (! empty($this->items)) { ?>
    <<?= $tag ?> class="<?= classes(
        'cards',
        'wp-block',
        'animate',
        \Gust\Helpers::isTaxonomy() ? 'cards--taxonomy-term-grid' : null,
        $this->classes,
    ) ?>" <?= attributes($this->attributes) ?>>
        <div class="cards__inner <?= $is_carousel ? 'content-width-fluid-lg' : 'content-width-lg' ?>">
            <?php if (! empty($this->heading) || ! empty($this->subheading)) { ?>
                <div class="cards__header">
                    <?php if (! empty($this->heading)) { ?>
                        <?= \Gust\Components\Heading::make(
                            heading: $this->heading,
                            classes: ['cards__heading', 'type-h1'],
                        ); ?>
                    <?php } ?>

                    <?php if (! empty($this->subheading)) { ?>
                        <div class="cards__subheading">
                            <?= wp_kses_post($this->subheading); ?>
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>

            <?php if ($is_carousel) { ?>
                <div class="cards__swiper-wrap">
                    <?php if (count($this->items) > 1) { ?>
                        <button type="button" class="cards__prev btn btn--ghost" aria-label="Previous slide">
                            <span class="btn__icon" style="--btn--icon: url('<?= staticUrl('images/icons/chevron-right.svg') ?>')"></span>
                        </button>

                        <button type="button" class="cards__next btn btn--ghost" aria-label="Next slide">
                            <span class="btn__icon" style="--btn--icon: url('<?= staticUrl('images/icons/chevron-right.svg') ?>')"></span>
                        </button>
                    <?php } ?>

                    <div class="cards__swiper swiper">
                        <div class="cards__items swiper-wrapper">
                            <?php foreach ($this->items as $key => $card) { ?>
                                <div class="cards__slide swiper-slide">
                                    <?= \Gust\Components\Card::make(...$card); ?>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            <?php } else { ?>
                <div class="cards__items">
                    <?php foreach ($this->items as $key => $card) { ?>
                        <?= \Gust\Components\Card::make(...$card); ?>
                    <?php } ?>
                </div>
            <?php } ?>

            <?php if (! empty($this->button['url']) && ! empty($this->button['title'])) { ?>
                <div class="cards__footer">
                    <div class="cards__more-link">
                        <?= \Gust\Components\Link::make(
                            title: $this->button['title'],
                            url: $this->button['url'],
                            target: $this->button['target'] ?? null,
                            classes: ['btn', 'btn--theme-2'],
                        ); ?>
                    </div>
                </div>
            <?php } ?>
        </div>
    </<?= $tag ?>>
<?php } ?>
