<?php

/**
 * ImageFullWidth Component Examples
 *
 * Note: Requires valid WordPress attachment IDs. Examples below use placeholder IDs.
 */

use Gust\Components\ImageFullWidth;

// Try to get a real attachment ID from the media library
$attachments = get_posts([
    'post_type' => 'attachment',
    'post_mime_type' => 'image',
    'posts_per_page' => 1,
    'post_status' => 'inherit',
]);
$sample_id = ! empty($attachments) ? $attachments[0]->ID : null;

?>

<section class="component-example-section">
    <h2 class="component-example-section__title">Full-width Image</h2>
    <p class="component-example-section__description">Image with full viewport width and responsive height (600px max, square below 600px).</p>
    <div class="component-example-section__preview">
        <?php if ($sample_id) { ?>
            <?= ImageFullWidth::make(id: $sample_id); ?>
        <?php } else { ?>
            <p><em>No images in media library. Upload an image to see this example.</em></p>
        <?php } ?>
    </div>
</section>