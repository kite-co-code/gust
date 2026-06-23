<?php

/**
 * TripCard Component Examples
 */

use Gust\Components\TripCard;

// Get real trip posts for preview
$trips = get_posts([
    'post_type' => 'trip',
    'posts_per_page' => 3,
    'post_status' => 'publish',
]);

?>

<?php if (! empty($trips)) { ?>
    <section class="component-example-section">
        <h2 class="component-example-section__title">Trip Card - From Posts</h2>
        <p class="component-example-section__description">Rendered from real trip posts with dates, location, skill levels, and pricing.</p>
        <div class="component-example-section__preview" style="display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 1.5rem; max-width: 72rem;">
            <?php foreach ($trips as $trip) { ?>
                <?= TripCard::make(object: $trip); ?>
            <?php } ?>
        </div>
    </section>
<?php } else { ?>
    <section class="component-example-section">
        <h2 class="component-example-section__title">Trip Card</h2>
        <p class="component-example-section__description">No trip posts found. Create some trips to preview this component.</p>
    </section>
<?php } ?>
