<header class="<?= classes('homepage-hero-header', 'wp-block', 'alignfull', 'not-prose', $this->classes) ?>" <?= attributes($this->attributes) ?>>
    <?php if (! empty($this->image) && $this->image_position === 'background') { ?>
        <div class="homepage-hero-header__bg-image" aria-hidden="true">
            <?= $this->image; ?>
        </div>
    <?php } ?>
    <?php if ($this->show_breadcrumbs) { ?>
        <?= \Gust\Components\Breadcrumbs::make(); ?>
    <?php } ?>

    <div class="homepage-hero-header__inner">
        <?php if (! empty($this->image) && $this->image_position === 'mini') { ?>
            <div class="homepage-hero-header__mini-image">
                <div class="homepage-hero-header__mini-image-inner img-fit">
                    <?= $this->image; ?>
                </div>
            </div>
        <?php } ?>

        <div class="homepage-hero-header__content">
            <?php if (($this->type ?? '') === 'guide') { ?>
                <?php if (! empty($this->subheading)) { ?>
                    <div class="homepage-hero-header__subheading">
                        <?= wp_kses_post($this->subheading); ?>
                    </div>
                <?php } ?>
            <?php } ?>

            <?php if (! empty($this->heading)) { ?>
                <?= \Gust\Components\Heading::make(...$this->heading); ?>
            <?php } ?>

            <?php if (($this->type ?? '') !== 'guide') { ?>
                <?php if (! empty($this->subheading)) { ?>
                    <div class="homepage-hero-header__subheading">
                        <?= wp_kses_post($this->subheading); ?>
                    </div>
                <?php } ?>
            <?php } ?>

            <?php if (! empty($this->meta)) { ?>
                <div class="homepage-hero-header__meta is-style-type-meta">
                    <?= wp_kses_post($this->meta); ?>
                </div>
            <?php } ?>

            <?php if (! empty($this->labels)) { ?>
                <div class="homepage-hero-header__labels">
                    <div class="homepage-hero-header__labels__items flex-list">
                        <?php foreach ($this->labels as $label) {
                            echo \Gust\Components\Link::make(
                                title: $label['name'],
                                url: $label['url'],
                                classes: [
                                    'btn',
                                    'btn--label',
                                ],
                            );
                        } ?>
                    </div>
                </div>
            <?php } ?>

            <?php if (! empty($this->buttons)) { ?>
                <div class="homepage-hero-header__buttons">
                    <ul class="flex-list">
                        <?php foreach ($this->buttons as $button) { ?>
                            <li class="flex-list__item">
                                <?= \Gust\Components\Link::make(...$button); ?>
                            </li>
                        <?php } ?>
                    </ul>
                </div>
            <?php } ?>
        </div>

        <?php if (! empty($this->image) && $this->image_position === 'inset') { ?>
            <div class="homepage-hero-header__inset-image">
                <div class="homepage-hero-header__inset-image-inner img-fit">
                    <?= $this->image; ?>
                </div>
            </div>
        <?php } ?>
    </div>
</header>
