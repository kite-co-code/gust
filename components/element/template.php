<?php if (! empty($this->content)) { ?>
    <<?= tag_escape($this->el); ?> class="<?= classes('element', $this->classes) ?>" <?= attributes($this->attributes) ?>><?php
        $filter = $this->content_filter;
    echo is_callable($filter) ? $filter($this->content) : $this->content;
    ?></<?= tag_escape($this->el); ?>>
<?php }
