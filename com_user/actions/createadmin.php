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

if (!$config->com_user->create_admin) {
    display_error('Creating admin user has been disabled.');
    return;
}
if ($_REQUEST['secret'] != $config->com_user->create_admin_secret) {
    display_error('Wrong secret.');
    return;
}

$new_admin_user = new user;
$new_admin_user->name = 'admin';
$new_admin_user->username = (empty($_REQUEST['username']) ? 'admin' : $_REQUEST['username']);
$new_admin_user->password('password');
$new_admin_user->abilities = array('system/all');

if ( !is_null($config->user_manager->get_user_by_username($new_admin_user->username)) ) {
    display_error('Username already exists!');
    return;
}

pines_log("Created admin user $new_admin_user->username.", 'notice');
$new_admin_user->save();

display_notice('Admin user created.');

?>