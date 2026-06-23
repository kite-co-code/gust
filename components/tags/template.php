<?php
/**
 * Tags Template
 *
 * @var \Gust\Components\Tags $this
 */

use Gust\Helpers;
?>

<div class="<?= classes('tags', 'wp-block', $this->classes) ?>" <?= attributes($this->attributes) ?>>
    <?php foreach ($this->tags as $tag) { ?>
        <span class="<?= classes('tags__item') ?>"><?= esc_html($tag) ?></span>
    <?php } ?>
</div>
