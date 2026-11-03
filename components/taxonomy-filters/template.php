<?php if (! empty($this->items)) { ?>
    <nav
        class="<?= classes('taxonomy-filters', 'wp-block', $this->classes) ?>"
        <?php if (! empty($this->label)) { ?>aria-labelledby="<?= esc_attr($this->label_id) ?>"<?php } ?>
        <?= attributes($this->attributes) ?>>
        <?php if (! empty($this->label)) { ?>
            <div class="taxonomy-filters__label">
                <span id="<?= esc_attr($this->label_id) ?>"><?= wp_kses_post($this->label) ?></span>
            </div>
        <?php } ?>

        <ul class="taxonomy-filters__list flex-list">
            <?php foreach ($this->items as $item) { ?>
                <li class="taxonomy-filters__item-wrap">
                    <?= \Gust\Components\Link::make(...$item); ?>
                </li>
            <?php }  ?>
        </ul>
    </nav>
<?php } ?>
