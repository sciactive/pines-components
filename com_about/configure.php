<?php
defined('D_RUN') or die('Direct access prohibited');

$config->com_about = new DynamicConfig;

// Description of your installation.
$config->com_about->description = "This is the default installation for ".$config->option_title.".";

// Whether to show Dandelion's description underneath yours.
$config->com_about->describe_self = true;

?>