<?php
defined('D_RUN') or die('Direct access prohibited');

$config->user_manager->logout();
$config->user_manager->punt_user('You have been logged out.');

?>