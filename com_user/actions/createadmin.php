<?php
/**
 * Create an admin user.
 *
 * @package Pines
 * @subpackage com_user
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if (!$pines->config->com_user->create_admin) {
	display_notice('Creating admin user has been disabled.');
	return;
}
if ($_REQUEST['secret'] != $pines->config->com_user->create_admin_secret) {
	display_notice('Wrong secret.');
	return;
}

$new_admin_user = user::factory();
$new_admin_user->name = 'admin';
$new_admin_user->username = (empty($_REQUEST['username']) ? 'admin' : $_REQUEST['username']);
$new_admin_user->password('password');
$new_admin_user->abilities = array('system/all');

$test = user::factory($_REQUEST['username']);
if ( isset($test->guid) ) {
	display_notice('Username already exists!');
	return;
}

pines_log("Created admin user $new_admin_user->username.", 'notice');
$new_admin_user->save();

display_notice('Admin user created.');

?>