<div class="<?= classes('video-item', $this->classes) ?>" data-video-item <?= attributes($this->attributes) ?>>
    <button class="video-item__play-button js-video-item-play" type="button">
        <span class="screen-reader-text">
            <?= esc_html__('Play video', 'gust'); ?>
        </span>
    </button>

    <?php if (! empty($this->image)) { ?>
        <div class="video-item__media img-fit">
            <?= \Gust\Components\Image::make(...$this->image); ?>
        </div>
    <?php } ?>

    <dialog class="video-item__video" aria-label="<?= esc_attr($this->heading ?: __('Video', 'gust')); ?>">
        <div class="video-item__video-inner">
            <div class="video-item__video-wrap">
                <button class="video-item__video-close cross" type="button">
                    <span class="screen-reader-text">
                        <?= esc_html__('Close Video', 'gust') ?>
                    </span>
                </button>
                <?= wp_kses($this->video, \Gust\Components\VideoItem::allowedEmbedHtml()); ?>
            </div>
        </div>
    </dialog>
</div>
