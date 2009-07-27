<?php
defined('P_RUN') or die('Direct access prohibited');

if ( isset($config->ability_manager) ) {
	$config->ability_manager->add('com_about', 'show', 'About Page', 'User can see the about page.');
}

?>