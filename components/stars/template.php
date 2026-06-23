<?php
/**
 * Stars Template
 *
 * @var \Gust\Components\Stars $this
 */

use Gust\SVG;

?>

<figure class="<?= classes('star', 'wp-block', $this->classes) ?>" <?= attributes($this->attributes); ?>>
    <?php for ($i = 1; $i <= 5; $i++) : ?>
        <div class="star__<?= $i <= $this->args['stars'] ? 'filled' : 'empty' ?>" aria-hidden="true">
            <?= SVG::get(get_theme_file_path('assets/images/icons/star-' . ($i <= $this->args['stars'] ? 'filled' : 'empty') . '.svg'), ['asset' => false]); ?>
        </div>
    <?php endfor; ?>
</figure>
