<?php

/**
 * Stars Component Examples
 */

use Gust\Components\Stars;

?>

<section class="component-example-section">
    <h2 class="component-example-section__title">Basic Stars</h2>
    <p class="component-example-section__description">Stars component with different ratings.</p>
    <div class="component-example-section__preview">
        <div style="margin-bottom: 1rem;">
            <strong>0 stars:</strong>
            <?= Stars::make(stars: 0); ?>
        </div>
        <div style="margin-bottom: 1rem;">
            <strong>1 star:</strong>
            <?= Stars::make(stars: 1); ?>
        </div>
        <div style="margin-bottom: 1rem;">
            <strong>3 stars:</strong>
            <?= Stars::make(stars: 3); ?>
        </div>
        <div style="margin-bottom: 1rem;">
            <strong>5 stars:</strong>
            <?= Stars::make(stars: 5); ?>
        </div>
    </div>
</section>