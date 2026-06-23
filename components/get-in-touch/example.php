<?php

/**
 * GetInTouch Component Examples
 */

use Gust\Components\GetInTouch;

?>

<section class="component-example-section">
    <h2 class="component-example-section__title">Get In Touch</h2>
    <p class="component-example-section__description">Example rendering of the GetInTouch component with contact information.</p>
    <div class="component-example-section__preview">
        <?= GetInTouch::make(contacts: [
            ['icon' => 'phone', 'label' => 'Office', 'value' => '+44 (0)12 3456 7890', 'url' => 'tel:+441234567890'],
            ['icon' => 'whatsapp', 'label' => 'Message via WhatsApp', 'value' => '', 'url' => 'https://wa.me/441234567890'],
            ['icon' => 'email', 'label' => 'info@swimquest.uk.com', 'value' => '', 'url' => 'mailto:info@swimquest.uk.com'],
        ]); ?>
    </div>
</section>
