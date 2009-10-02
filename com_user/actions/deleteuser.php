<?php
/**
 * Delete a user.
 *
 * @package Pines
 * @subpackage com_user
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_user/delete') ) {
	$config->user_manager->punt_user("You don't have necessary permission.", $config->template->url('com_user', 'manageusers', null, false));
	return;
}

$com_user_list = explode(',', $_REQUEST['user_id']);
foreach ($com_user_list as $cur_user) {
    if ( !$config->user_manager->delete_user($cur_user) )
        $com_user_failed_deletes .= (empty($com_user_failed_deletes) ? '' : ', ').$cur_user;
}
if (empty($com_user_failed_deletes)) {
    display_notice('Selected user(s) deleted successfully.');
} else {
    display_error('Could not delete users with given IDs: '.$com_user_failed_deletes);
}

$config->user_manager->list_users();
?>