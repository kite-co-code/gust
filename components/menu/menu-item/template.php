<li class="<?= classes('menu-item', $this->classes) ?>" <?= attributes($this->attributes) ?>>
    <span class="menu-item__wrap">
        <?= \Gust\Components\Link::make(...$this->link); ?>

        <?php if ($this->display_submenu === true) { ?>
            <?= \Gust\Components\Button::make(...$this->button); ?>
        <?php } ?>
    </span>

    <?php if ($this->display_submenu === true) { ?>
        <div <?= \Gust\Helpers::buildAttributes($this->{'sub-menu-attributes'}) ?>>
            <div class="sub-menu__inner">
                <?php if ($this->depth === 0) { ?>
                    <p class="sub-menu__heading"><?= esc_html($this->link['content']) ?></p>
                <?php } ?>
                <?= \Gust\Components\Menu\MenuList::make(
                    items: $this->item->children,
                    depth: ($this->depth + 1),
                    max_depth: $this->max_depth,
                ); ?>
            </div>
        </div>
    <?php } ?>
</li>
