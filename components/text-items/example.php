<?php

/**
 * TextItems Component Examples
 *
 * Available variables:
 * - $faker: Faker\Generator instance for generating mock data
 * - $component: The current component name
 */

?>

<!-- Example: With meta labels -->
<section class="component-example-section">
    <h2 class="component-example-section__title">Text Items with Meta</h2>
    <p class="component-example-section__description">
        Repeater with meta label (e.g. Day 1), title, and description.
    </p>
    <div class="component-example-section__preview">
        <?= \Gust\Components\TextItems::make(
            heading: 'Itinerary',
            items: [
                [
                    'meta'        => 'Day 1',
                    'title'       => 'Arrive & Explore',
                    'description' => '<p>'.$faker->paragraph(2).'</p>',
                ],
                [
                    'meta'        => 'Day 2',
                    'title'       => 'Morning Dive',
                    'description' => '<p>'.$faker->paragraph(3).'</p>',
                ],
                [
                    'meta'        => 'Day 3',
                    'title'       => 'Departure',
                    'description' => '<p>'.$faker->paragraph(2).'</p>',
                ],
            ],
        ); ?>
    </div>
</section>

<!-- Example: Without meta -->
<section class="component-example-section">
    <h2 class="component-example-section__title">Text Items without Meta</h2>
    <p class="component-example-section__description">
        Just title and description — meta label is optional.
    </p>
    <div class="component-example-section__preview">
        <?= \Gust\Components\TextItems::make(
            heading: 'What\'s Included',
            items: [
                [
                    'title'       => 'All dive equipment',
                    'description' => '<p>'.$faker->paragraph(2).'</p>',
                ],
                [
                    'title'       => 'Accommodation',
                    'description' => '<p>'.$faker->paragraph(2).'</p>',
                ],
                [
                    'title'       => 'Full board meals',
                    'description' => '<p>'.$faker->paragraph(2).'</p>',
                ],
            ],
        ); ?>
    </div>
</section>
