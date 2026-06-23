<?php

/**
 * TestimonialCards Component Examples
 */

use Gust\Components\TestimonialCards;

$stories = get_posts([
    'post_type'      => 'story',
    'posts_per_page' => 3,
    'post_status'    => 'publish',
]);

?>

<section class="component-example-section">
    <h2 class="component-example-section__title">Testimonial Cards — Custom</h2>
    <p class="component-example-section__description">Editor-entered quotes, author, and star rating.</p>
    <div class="component-example-section__preview">
        <?= TestimonialCards::make(
            heading: 'What our swimmers say',
            items: [
                [
                    'stars'         => 5,
                    'quote'         => 'An absolutely life-changing experience. The coaching was world-class and the locations were breathtaking.',
                    'author_name'   => 'Sarah Mitchell',
                    'author_detail' => 'Open Water Swimmer, Edinburgh',
                ],
                [
                    'stars'         => 5,
                    'quote'         => 'I arrived nervous and left with a bronze medal and a group of lifelong friends. SwimQuest delivers every time.',
                    'author_name'   => 'Tom Davies',
                    'author_detail' => 'Triathlete, Bristol',
                ],
                [
                    'stars'         => 5,
                    'quote'         => 'The Corfu week was simply magical. Professional, warm, and perfectly organised from start to finish.',
                    'author_name'   => 'Helen Park',
                    'author_detail' => 'Swimmer, London',
                ],
            ],
        ); ?>
    </div>
</section>

<?php if (! empty($stories)) { ?>
<section class="component-example-section">
    <h2 class="component-example-section__title">Testimonial Cards — From Stories</h2>
    <p class="component-example-section__description">Story titles used as quotes with manually set star ratings.</p>
    <div class="component-example-section__preview">
        <?= TestimonialCards::make(
            heading: 'Swimmer Stories',
            items: array_map(fn ($story) => [
                'stars'       => 5,
                'quote'       => get_the_title($story->ID),
                'author_name' => get_the_author_meta('display_name', $story->post_author),
                'url'         => get_the_permalink($story->ID),
                'image'       => has_post_thumbnail($story->ID)
                    ? ['ID' => get_post_thumbnail_id($story->ID), 'size' => 'thumbnail']
                    : null,
            ], $stories),
        ); ?>
    </div>
</section>
<?php } ?>
