<section class="<?= classes('accordion', 'wp-block', $this->classes) ?>" <?= attributes($this->attributes) ?>>
    <div class="accordion__inner content-width-sm">
        <?php if (! empty($this->heading)) { ?>
            <div class="accordion__header">
                <?= \Gust\Components\Heading::make(...$this->heading); ?>
            </div>
        <?php } ?>

        <?php if (! empty($this->accordion_items)) { ?>
            <div class="accordion__items">
                <?php foreach ($this->accordion_items as $key => $item) { ?>
                    <div class="accordion__item">
                        <h3 class="accordion__item__heading">
                            <button <?= \Gust\Helpers::buildAttributes($item['button_attributes']); ?>>
                                <span class="accordion__item__title"><?= wp_kses_post($item['title']); ?></span>
                            </button>
                        </h3>

                        <div <?= \Gust\Helpers::buildAttributes($item['panel_attributes']); ?>>
                            <div class="accordion__item__panel-inner">
                                <?= wp_kses_post($item['content']) ?>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>
    </div>
</section>
