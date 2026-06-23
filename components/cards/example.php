<?php

/**
 * Cards Component Examples
 */

use Gust\Components\Cards;

// Get sample image
$attachments = get_posts([
    'post_type' => 'attachment',
    'post_mime_type' => 'image',
    'posts_per_page' => 3,
    'post_status' => 'inherit',
]);
$image_ids = array_map(fn ($a) => $a->ID, $attachments);

?>

<section class="component-example-section">
    <h2 class="component-example-section__title">Cards from Recent Posts</h2>
    <p class="component-example-section__description">Grid of cards populated from recent posts (uses <code>card_source: 'recent'</code>).</p>
    <div class="component-example-section__preview">
        <?= Cards::make(
            card_source: 'recent',
            post_type: 'page',
            limit: 3,
            columns: '3',
        ); ?>
    </div>
</section>

<section class="component-example-section">
    <h2 class="component-example-section__title">Cards with Custom Content</h2>
    <p class="component-example-section__description">Cards with manually defined content and links.</p>
    <div class="component-example-section__preview">
        <?= Cards::make(
            columns: '3',
            items: [
                [
                    'content' => [
                        'heading' => 'Feature One',
                        'text' => 'Description of the first feature.',
                        'link' => ['url' => '/feature-1', 'title' => 'Learn more'],
                    ],
                ],
                [
                    'content' => [
                        'heading' => 'Feature Two',
                        'text' => 'Description of the second feature.',
                        'link' => ['url' => '/feature-2', 'title' => 'Learn more'],
                    ],
                ],
                [
                    'content' => [
                        'heading' => 'Feature Three',
                        'text' => 'Description of the third feature.',
                        'link' => ['url' => '/feature-3', 'title' => 'Learn more'],
                    ],
                ],
            ],
        ); ?>
    </div>
</section>

<section class="component-example-section">
    <h2 class="component-example-section__title">Cards with Images</h2>
    <p class="component-example-section__description">Cards with images from the media library.</p>
    <div class="component-example-section__preview">
        <?php if (count($image_ids) >= 3) { ?>
            <?= Cards::make(
                columns: '3',
                items: [
                    [
                        'content' => [
                            'heading' => 'Destination One',
                            'text' => 'A beautiful location to explore.',
                            'image' => ['id' => $image_ids[0]],
                            'link' => ['url' => '#', 'title' => 'Explore'],
                        ],
                    ],
                    [
                        'content' => [
                            'heading' => 'Destination Two',
                            'text' => 'Another amazing place to visit.',
                            'image' => ['id' => $image_ids[1]],
                            'link' => ['url' => '#', 'title' => 'Explore'],
                        ],
                    ],
                    [
                        'content' => [
                            'heading' => 'Destination Three',
                            'text' => 'Discover something new.',
                            'image' => ['id' => $image_ids[2]],
                            'link' => ['url' => '#', 'title' => 'Explore'],
                        ],
                    ],
                ],
            ); ?>
        <?php } else { ?>
            <p><em>Upload at least 3 images to the media library to see this example.</em></p>
        <?php } ?>
    </div>
</section>

<section class="component-example-section">
    <h2 class="component-example-section__title">Cards with Square Images (<code>image_size</code>)</h2>
    <p class="component-example-section__description">Per-card <code>image_size: 'gust_card_square'</code> (800&times;800 hard crop). This is the recommended size for card grids.</p>
    <div class="component-example-section__preview">
        <?php if (count($image_ids) >= 3) { ?>
            <?= Cards::make(
                columns: '3',
                items: [
                    [
                        'image_size' => 'gust_card_square',
                        'content' => [
                            'heading' => 'Square Card One',
                            'text' => 'Uses gust_card_square image size.',
                            'image' => ['id' => $image_ids[0]],
                        ],
                    ],
                    [
                        'image_size' => 'gust_card_square',
                        'content' => [
                            'heading' => 'Square Card Two',
                            'text' => 'Uses gust_card_square image size.',
                            'image' => ['id' => $image_ids[1]],
                        ],
                    ],
                    [
                        'image_size' => 'gust_card_square',
                        'content' => [
                            'heading' => 'Square Card Three',
                            'text' => 'Uses gust_card_square image size.',
                            'image' => ['id' => $image_ids[2]],
                        ],
                    ],
                ],
            ); ?>
        <?php } else { ?>
            <p><em>Upload at least 3 images to the media library to see this example.</em></p>
        <?php } ?>
    </div>
</section>

<section class="component-example-section">
    <h2 class="component-example-section__title">Horizontal Cards</h2>
    <p class="component-example-section__description">Horizontal card layout with image left, text right. Uses <code>type: 'horizontal'</code>.</p>
    <div class="component-example-section__preview">
        <?php if (count($image_ids) >= 2) { ?>
            <?= Cards::make(
                type: 'horizontal',
                items: [
                    [
                        'content' => [
                            'heading' => 'Horizontal Card One',
                            'text' => 'Image on the left, text on the right.',
                            'image' => ['id' => $image_ids[0]],
                            'link' => ['url' => '#', 'title' => 'Explore'],
                        ],
                    ],
                    [
                        'content' => [
                            'heading' => 'Horizontal Card Two',
                            'text' => 'A two-column grid of horizontal cards.',
                            'image' => ['id' => $image_ids[1]],
                            'link' => ['url' => '#', 'title' => 'Explore'],
                        ],
                    ],
                ],
            ); ?>
        <?php } else { ?>
            <p><em>Upload at least 2 images to the media library to see this example.</em></p>
        <?php } ?>
    </div>
</section>

<section class="component-example-section">
    <h2 class="component-example-section__title">Two Column Cards</h2>
    <p class="component-example-section__description">Cards in a two-column layout.</p>
    <div class="component-example-section__preview">
        <?= Cards::make(
            columns: '2',
            items: [
                [
                    'content' => [
                        'heading' => 'Left Card',
                        'text' => 'Content for the left card.',
                    ],
                ],
                [
                    'content' => [
                        'heading' => 'Right Card',
                        'text' => 'Content for the right card.',
                    ],
                ],
            ],
        ); ?>
    </div>
</section>
