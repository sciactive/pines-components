<?php
/**
 * Save an employee adjustment.
 *
 * @package Components\hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Kirk Johnson <kirk@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_hrm/editadjustment') )
	punt_user(null, pines_url('com_hrm', 'adjustment/list'));

if (isset($_REQUEST['id']) && (int) $_REQUEST['id'] != 0) {
	$adjustment = com_hrm_adjustment::factory((int) $_REQUEST['id']);
	if (!isset($adjustment->guid)) {
		pines_error('Requested adjustment id is not accessible.');
		return;
	}
} else {
	$adjustment = com_hrm_adjustment::factory();
}

$adjustment->name = $_REQUEST['name'];
$adjustment->employee = user::factory(intval($_REQUEST['employee']));
$adjustment->location = $adjustment->employee->group->guid ? $adjustment->employee->group : $_SESSION['user']->group;
$adjustment->amount = (float) $_REQUEST['amount'];
$adjustment->date = strtotime($_REQUEST['date']);
$adjustment->comments = $_REQUEST['comments'];

if (empty($adjustment->name)) {
	pines_notice('Please provide a name for the adjustment.');
	pines_redirect(pines_url('com_hrm', 'adjustment/list'));
	return;
}
if (empty($adjustment->amount)) {
	pines_notice('Please provide an amount for the adjustment.');
	pines_redirect(pines_url('com_hrm', 'adjustment/list'));
	return;
}

if ($adjustment->save()) {
	pines_notice('Saved adjustment ['.$adjustment->name.']');
} else {
	pines_error('Error saving adjustment. Do you have permission?');
}

pines_redirect(pines_url('com_hrm', 'adjustment/list'));

?>