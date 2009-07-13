<div class="module<?php echo $this->class_suffix; ?>">
    <?php if ($this->show_title && !empty($this->title)) { ?>
    <div class="module_title"><?php echo $this->title; ?></div>
    <?php } ?>
    <?php echo $this->content; ?>
</div>