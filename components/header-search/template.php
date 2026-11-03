<form class="<?= classes($this->classes) ?>" <?= attributes($this->attributes) ?>>
    <label class="screen-reader-text" for="<?= esc_attr($this->input_id); ?>">
        <?= esc_html__('Search', 'gust'); ?>
    </label>

    <div class="header-search__group input-group">
        <input
            id="<?= esc_attr($this->input_id); ?>"
            class="header-search__input"
            type="search"
            name="s"
            placeholder="<?= esc_attr__('Search...', 'gust'); ?>"
            required
        >

        <button class="header-search__submit btn" type="submit">
            <span class="header-search__submit__text">
                <?= esc_html__('Search', 'gust'); ?>
            </span>
        </button>
    </div>
</form>
