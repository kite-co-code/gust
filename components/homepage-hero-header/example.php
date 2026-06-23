<?php

/**
 * HomepageHeroHeader Component Examples
 */

use Gust\Components\HomepageHeroHeader;

?>

<section class="component-example-section">
    <h2 class="component-example-section__title">Basic Page Header</h2>
    <p class="component-example-section__description">Page header with heading.</p>
    <div class="component-example-section__preview">
        <?= HomepageHeroHeader::make(
            heading: 'Welcome to Our Site',
            show_breadcrumbs: false,
        ); ?>
    </div>
</section>

<section class="component-example-section">
    <h2 class="component-example-section__title">Page Header with Subheading</h2>
    <p class="component-example-section__description">Page header with heading and subheading.</p>
    <div class="component-example-section__preview">
        <?= HomepageHeroHeader::make(
            heading: 'About Us',
            subheading: 'Learn more about our company and mission',
            show_breadcrumbs: false,
        ); ?>
    </div>
</section>

<section class="component-example-section">
    <h2 class="component-example-section__title">Page Header with Background</h2>
    <p class="component-example-section__description">Page header with brand background color.</p>
    <div class="component-example-section__preview">
        <?= HomepageHeroHeader::make(
            heading: 'Services',
            subheading: 'What we offer',
            background: 'brand-2',
            show_breadcrumbs: false,
        ); ?>
    </div>
</section>

<section class="component-example-section">
    <h2 class="component-example-section__title">Page Header with Background</h2>
    <p class="component-example-section__description">Page header with brand background color.</p>
    <div class="component-example-section__preview">
        <?= HomepageHeroHeader::make(
            heading: 'Nick Ayers',
            type: 'guide',
            subheading: 'Meet our team',
            background: 'color-context-white',
            show_breadcrumbs: false,
        ); ?>
    </div>
</section>
