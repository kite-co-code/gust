<?php

/**
 * Promo Component Examples
 */

use Gust\Components\Promo;

?>

<section class="component-example-section">
    <h2 class="component-example-section__title">Promo — Default</h2>
    <p class="component-example-section__description">With title, subheading and button.</p>
    <div class="component-example-section__preview">
        <?= Promo::make(
            title: 'What is a Swimming holiday?',
            subheading: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna.',
            link: ['url' => '#', 'title' => 'Find Out More', 'target' => ''],
        ); ?>
    </div>
</section>

<section class="component-example-section">
    <h2 class="component-example-section__title">Promo — Title Only</h2>
    <div class="component-example-section__preview">
        <?= Promo::make(
            title: 'What is a Swimming holiday?',
            link: ['url' => '#', 'title' => 'Find Out More', 'target' => ''],
        ); ?>
    </div>
</section>
