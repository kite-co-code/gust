<?php

/**
 * PageHeader Component Examples
 */

use Gust\Components\PageHeader;

?>

<section class="component-example-section">
    <h2 class="component-example-section__title">Basic Page Header</h2>
    <p class="component-example-section__description">Page header with heading.</p>
    <div class="component-example-section__preview">
        <?= PageHeader::make(
            heading: 'Welcome to Our Site',
            show_breadcrumbs: false,
        ); ?>
    </div>
</section>

<section class="component-example-section">
    <h2 class="component-example-section__title">Page Header with Subheading</h2>
    <p class="component-example-section__description">Page header with heading and subheading.</p>
    <div class="component-example-section__preview">
        <?= PageHeader::make(
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
        <?= PageHeader::make(
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
        <?= PageHeader::make(
            heading: 'Nick Ayers',
            type: 'guide',
            subheading: 'Meet our team',
            background: 'color-context-white',
            show_breadcrumbs: false,
        ); ?>
    </div>
</section>

<section class="component-example-section">
    <h2 class="component-example-section__title">Page Header — Left Aligned</h2>
    <p class="component-example-section__description">White background, breadcrumb and heading flush left. Applied automatically to accommodation and itinerary single posts.</p>
    <div class="component-example-section__preview">
        <?= PageHeader::make(
            heading: 'Hotel Paradise',
            subheading: 'A boutique stay in the heart of the village.',
            background: 'none',
            classes: ['page-header--align-left'],
        ); ?>
    </div>
</section>

<?php
$hero_image_id = (int) (\get_option('page_on_front')
    ? \get_post_thumbnail_id(\get_option('page_on_front'))
    : 0);
if (! $hero_image_id) {
    $attachments = \get_posts([
        'post_type' => 'attachment',
        'post_mime_type' => 'image',
        'posts_per_page' => 1,
        'fields' => 'ids',
    ]);
    $hero_image_id = $attachments[0] ?? 0;
}
?>

<section class="component-example-section">
    <h2 class="component-example-section__title">Page Header with Hero Image</h2>
    <p class="component-example-section__description">Full-bleed featured image variant. Heading and subheading overlay the image with a bottom gradient.</p>
    <div class="component-example-section__preview">
        <?= PageHeader::make(
            heading: 'Mathraki Island',
            subheading: 'Seven days of guided swimming in the Ionian.',
            image: $hero_image_id ?: null,
            image_position: 'hero',
            show_breadcrumbs: false,
        ); ?>
    </div>
</section>

<?php
$square_image_id = 0;
$attachments = \get_posts([
    'post_type' => 'attachment',
    'post_mime_type' => 'image',
    'posts_per_page' => 1,
    'fields' => 'ids',
]);
$square_image_id = $attachments[0] ?? 0;
?>

<section class="component-example-section">
    <h2 class="component-example-section__title">Guide Page Header with Square Image</h2>
    <p class="component-example-section__description">Guide layout with a 300px square featured image rendered alongside the heading.</p>
    <div class="component-example-section__preview">
        <?= PageHeader::make(
            heading: 'Nick Ayers',
            type: 'guide',
            subheading: 'Meet our team',
            image: $square_image_id ?: null,
            image_position: 'square',
            background: 'color-context-white',
            show_breadcrumbs: false,
        ); ?>
    </div>
</section>
