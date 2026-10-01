<header class="<?= classes('site-header', $this->classes) ?>" data-site-header <?= attributes($this->attributes) ?>>
    <div class="site-header__bar">
        <?= \Gust\Components\Link::make(
            url: home_url('/'),
            classes: ['site-header__logo', 'img-fit'],
            title: \Gust\Image::get('logo-alt.svg', [
                'alt' => get_bloginfo('name'),
                'loading' => false,
                'attributes' => [
                    'data-spai-eager' => class_exists('\\ShortPixelAI') ? 'true' : null,
                ],
            ]),
            content_filter: '',
        ); ?>

        <div class="site-header__buttons">
            <button
                class="site-header__toggle site-header__search-toggler btn btn--ghost"
                popovertarget="site-header-search-panel"
                type="button">
                <span class="site-header__toggle-closed btn__icon" style="--btn--icon: url('<?= staticUrl('images/icons/search.svg') ?>')"></span>
                <?= \Gust\Components\Burger::make(
                    classes: ['site-header__toggle-open', 'is-open'],
                    attributes: ['aria-hidden' => 'true']
                ); ?>
                <span class="sr-only"><?= __('Search', 'gust'); ?></span>
                <span class="site-header__toggle-closed" aria-hidden="true"><?= __('Search', 'gust'); ?></span>
                <span class="site-header__toggle-open" aria-hidden="true"><?= __('Close', 'gust'); ?></span>
            </button>

            <button
                class="site-header__toggle site-header__burger btn btn--ghost"
                popovertarget="site-header-nav"
                type="button">
                <?= \Gust\Components\Burger::make(attributes: ['aria-hidden' => 'true']); ?>
                <span class="sr-only"><?= __('Menu', 'gust') ?></span>
                <span class="site-header__toggle-closed" aria-hidden="true"><?= __('Menu', 'gust') ?></span>
                <span class="site-header__toggle-open" aria-hidden="true"><?= __('Close', 'gust') ?></span>
            </button>
        </div>

        <?php /* Inline from the site-header breakpoint up, a popover panel below it */ ?>
        <div class="site-header__nav site-header__panel" id="site-header-nav" popover data-scroll-lock>
            <?= \Gust\Components\Menu::make(
                theme_location: 'header',
                menu_id: 'main-menu',
                classes: ['site-header__navigation'],
            ); ?>

            <div class="site-header__search-desktop-wrapper">
                <button
                    class="site-header__search-toggler btn btn--ghost"
                    aria-expanded="false"
                    aria-controls="site-header-search-desktop"
                    type="button">
                    <span class="btn__icon" data-show-collapsed style="--btn--icon: url('<?= staticUrl('images/icons/search.svg') ?>')"><span class="sr-only"><?= __('Open search', 'gust'); ?></span></span>
                    <span class="btn__icon" data-show-expanded style="--btn--icon: url('<?= staticUrl('images/icons/close.svg') ?>')"><span class="sr-only"><?= __('Close search', 'gust'); ?></span></span>
                </button>

                <div class="site-header__search-desktop" id="site-header-search-desktop" hidden aria-hidden="true">
                    <?= \Gust\Components\HeaderSearch::make(); ?>
                </div>
            </div>

            <?php if (! empty($this->content['call_to_action_1'])) { ?>
                <?= \Gust\Components\Link::make(...$this->content['call_to_action_1']); ?>
            <?php } ?>
        </div>
    </div>

    <div class="site-header__panel site-header__search-panel" id="site-header-search-panel" popover data-scroll-lock>
        <?= \Gust\Components\HeaderSearch::make(); ?>
    </div>
</header>
