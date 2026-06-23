<?php

use Gust\SVG;

?>

<section id="trip-includes" class="<?= classes('trip-includes', 'wp-block', $this->classes) ?>" <?= attributes($this->attributes) ?>>
    <div class="trip-includes__inner content-width-sm align-left w-full">
        <?= \Gust\Components\Heading::make(
            el: 'h4',
            heading: __("What's included", 'gust'),
            classes: ['trip-includes__heading'],
        ); ?>

        <div class="trip-includes__columns">
            <?php if (! empty($this->included_items)) { ?>
                <div class="trip-includes__list">
                    <h6><?= esc_html__('Included', 'gust'); ?></h6>
                    <ul>
                        <?php foreach ($this->included_items as $item) { ?>
                            <li>
                                <span class="trip-includes__icon" aria-hidden="true">
                                    <?= SVG::get(get_theme_file_path('assets/images/icons/tick.svg'), ['asset' => false, 'width' => 16, 'height' => 16]); ?>
                                </span>
                                <span><?= esc_html($item); ?></span>
                            </li>
                        <?php } ?>
                    </ul>
                </div>
            <?php } ?>

            <?php if (! empty($this->not_included_items)) { ?>
                <div class="trip-includes__list trip-includes__list--not-included">
                    <h6><?= esc_html__('Not included', 'gust'); ?></h6>
                    <ul>
                        <?php foreach ($this->not_included_items as $item) { ?>
                            <li>
                                <span class="trip-includes__icon" aria-hidden="true">
                                    <?= SVG::get(get_theme_file_path('assets/images/icons/cross.svg'), ['asset' => false, 'width' => 10, 'height' => 10]); ?>
                                </span>
                                <span><?= esc_html($item); ?></span>
                            </li>
                        <?php } ?>
                    </ul>
                </div>
            <?php } ?>
        </div>
    </div>
</section>
