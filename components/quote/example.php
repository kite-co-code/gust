<?php

/**
 * Quote Component Examples
 */

use Gust\Components\Quote;

?>

<section class="component-example-section">
    <h2 class="component-example-section__title">Basic Quote</h2>
    <p class="component-example-section__description">Quote with credit and author line.</p>
    <div class="component-example-section__preview">
        <?= Quote::make(
            quote: 'I love everything about a swim tour: meeting new people, hearing stories of life, expanding experiences through water and discovering new exciting locations to take a dip in!',
            credit: 'Nick Ayers',
            role: 'Guide & Coach',
        ); ?>
    </div>
</section>
