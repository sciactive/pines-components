<?php
defined('D_RUN') or die('Direct access prohibited');

$config->com_newsletter = new DynamicConfig;

// This is the default "from" email.
$config->com_newsletter->default_from = "Nowhere <nowhere@example.com>";

// This is the "reply-to" email.
$config->com_newsletter->default_reply_to = "webmaster@example.com";

?>
