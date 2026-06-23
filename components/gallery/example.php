<?php

/**
 * Gallery Component Examples
 */

use Gust\Components\Gallery;

$attachments = get_posts([
    'post_type' => 'attachment',
    'post_mime_type' => 'image',
    'posts_per_page' => -1,
    'post_status' => 'inherit',
]);

$image_items = array_map(fn ($a) => ['image' => ['id' => $a->ID]], $attachments);

?>

<section class="component-example-section">
    <h2 class="component-example-section__title">Swiper-powered image carousel</h2>
    <p class="component-example-section__description">Gallery with no heading; navigation still shows when multiple images are present.</p>
    <div class="component-example-section__preview">
        <?php if (count($image_items) >= 2) { ?>
            <?= Gallery::make(
                images: $image_items,
            ); ?>
        <?php } else { ?>
            <p><em>Upload at least 2 images to the media library to see this example.</em></p>
        <?php } ?>
    </div>
</section>
