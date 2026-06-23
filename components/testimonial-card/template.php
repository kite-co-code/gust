<?php
/**
 * TestimonialCard Template
 *
 * @var \Gust\Components\TestimonialCard $this
 */
?>

<article class="<?= classes('testimonial-card', 'animate-element', $this->classes) ?>" <?= attributes($this->attributes) ?>>
    <div class="testimonial-card__top">
        <?= \Gust\Components\Stars::make(stars: $this->stars); ?>

        <blockquote class="testimonial-card__quote">
            <?php if (! empty($this->url)) { ?>
                <a class="testimonial-card__link" href="<?= esc_url($this->url) ?>">
                    <?= esc_html($this->quote) ?>
                </a>
            <?php } else { ?>
                <?= esc_html($this->quote) ?>
            <?php } ?>
        </blockquote>
    </div>

    <?php if (! empty($this->author_name)) { ?>
        <div class="testimonial-card__author">
            <?php if (! empty($this->image)) { ?>
                <div class="testimonial-card__image">
                    <div class="testimonial-card__image-inner img-fit">
                        <?= \Gust\Components\Image::make(...$this->image); ?>
                    </div>
                </div>
            <?php } ?>

            <div class="testimonial-card__author-text">
                <cite class="testimonial-card__author-name"><?= esc_html($this->author_name) ?></cite>
                <?php if (! empty($this->author_detail)) { ?>
                    <span class="testimonial-card__author-detail"><?= esc_html($this->author_detail) ?></span>
                <?php } ?>
            </div>
        </div>
    <?php } ?>
</article>
