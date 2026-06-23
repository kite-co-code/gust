<?= \Gust\Components\Cards::make(
    heading: __('Related stories', 'gust'),
    items: $this->items,
    type: 'horizontal',
    card_background_color: 'none',
    classes: ['trip-related-stories', 'color-context-neutral'],
); ?>
