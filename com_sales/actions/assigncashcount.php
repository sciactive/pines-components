<?php
/**
 * Assign a cash count to an employee.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if (!gatekeeper('com_sales/assigncashcount') )
	punt_user('You don\'t have necessary permission.', pines_url('com_sales', 'assigncashcount', array('location' => $_REQUEST['location'])));

$location = group::factory((int) $_REQUEST['location']);
if (!isset($location->guid))
	$location = null;

if (!isset($location)) {
	pines_error('Requested location id is not accessible.');
	$pines->com_sales->list_cashcounts();
	return;
}
$location->com_sales_task_cashcount = true;
if ($location->save()) {
	pines_notice('Cash Count Assigned to ['.$location->name.']');
} else {
	pines_error('Error saving cash count assignment. Do you have permission?');
}

redirect(pines_url('com_sales', 'listcashcounts'));

?>