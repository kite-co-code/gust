<?php
/**
 * ImageFullWidth Template
 *
 * @var \Gust\Components\ImageFullWidth $this
 */

use Gust\Helpers;
?>

<?php if (! empty($this->output)) { ?>
    <?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_get_attachment_image() output is already escaped ?>
    <?= $this->output; ?>
<?php } ?>
