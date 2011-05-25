<?php
/**
 * Save an employee bonus.
 *
 * @package Pines
 * @subpackage com_hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_hrm/editbonuses') )
	punt_user(null, pines_url('com_hrm', 'bonus/list'));

if (isset($_REQUEST['id']) && (int) $_REQUEST['id'] != 0) {
	$bonus = com_hrm_bonus::factory((int) $_REQUEST['id']);
	if (!isset($bonus->guid)) {
		pines_error('Requested bonus id is not accessible.');
		return;
	}
} else {
	$bonus = com_hrm_bonus::factory();
}

$bonus->name = $_REQUEST['name'];
$bonus->employee = user::factory(intval($_REQUEST['employee']));
$bonus->location = $bonus->employee->group->guid ? $bonus->employee->group : $_SESSION['user']->group;
$bonus->amount = (float) $_REQUEST['amount'];
$bonus->date = strtotime($_REQUEST['date']);
$bonus->comments = $_REQUEST['comments'];

if (empty($bonus->name)) {
	pines_notice('Please provide a name for the bonus.');
	redirect(pines_url('com_hrm', 'bonus/list'));
	return;
}
if (empty($bonus->amount)) {
	pines_notice('Please provide an amount for the bonus.');
	redirect(pines_url('com_hrm', 'bonus/list'));
	return;
}

if ($bonus->save()) {
	pines_notice('Saved bonus ['.$bonus->name.']');
} else {
	pines_error('Error saving bonus. Do you have permission?');
}

redirect(pines_url('com_hrm', 'bonus/list'));

?>