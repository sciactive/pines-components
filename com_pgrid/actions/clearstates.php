<?php
/**
 * Clear all Pines Grid states.
 *
 * @package Components\pgrid
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if (!gatekeeper())
	punt_user();

if ($_REQUEST['all_users'] == 'true') {
	// Get all the system users.
	if (!gatekeeper('com_pgrid/clearallstates'))
		punt_user(null, pines_url('com_pgrid', 'clear_states', array('all_users' => 'true')));
	$users = $pines->user_manager->get_users(true);
} else {
	// Just an array of the current user.
	$users = array($_SESSION['user']);
}

$success = true;
foreach ((array) $users as $cur_user) {
	// If they have no guid, they're not valid.
	if (!isset($cur_user->guid))
		continue;
	if (isset($cur_user->pgrid_saved_states)) {
		unset($cur_user->pgrid_saved_states);
		$success = $success && $cur_user->save();
	}
}

if ($success)
	pines_notice('Successfully removed pgrid states.');
else
	pines_error('There was an error while removing states. Do you have permission?');

pines_redirect(pines_url());

?>