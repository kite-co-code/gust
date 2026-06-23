<?php

/**
 * Tags Component Examples
 */

use Gust\Components\Tags;

?>

<section class="component-example-section">
    <h2 class="component-example-section__title">Button-like Tags</h2>
    <p class="component-example-section__description">Tags are rendered in a button-like style with consistent spacing.</p>
    <div class="component-example-section__preview" style="display: flex; gap: 1rem; flex-wrap: wrap;">
        <?= Tags::make(tags: ['Adventure', 'Family Friendly', 'Beach', 'Snorkelling']) ?>
    </div>

