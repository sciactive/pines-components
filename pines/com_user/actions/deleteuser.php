<?php
/**
 * Delete a user.
 *
 * @package Pines
 * @subpackage com_user
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_user/delete') )
	punt_user('You don\'t have necessary permission.', pines_url('com_user', 'listusers'));

$list = explode(',', $_REQUEST['id']);
foreach ($list as $cur_user) {
	$cur_entity = user::factory((int) $cur_user);
	if ( !isset($cur_entity->guid) || !$cur_entity->delete() )
		$failed_deletes .= (empty($failed_deletes) ? '' : ', ').$cur_user;
}
if (empty($failed_deletes)) {
	pines_notice('Selected user(s) deleted successfully.');
} else {
	pines_error('Could not delete users with given IDs: '.$failed_deletes);
}

redirect(pines_url('com_user', 'listusers'));

?>