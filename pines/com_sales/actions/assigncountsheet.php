<?php
/**
 * Assign a countsheet to an employee.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if (!gatekeeper('com_sales/assigncountsheet') )
	punt_user('You don\'t have necessary permission.', pines_url('com_sales', 'assigncountsheet', array('location' => $_REQUEST['location'])));

$location = group::factory((int) $_REQUEST['location']);
if (!isset($location->guid))
	$location = null;

if (!isset($location)) {
	pines_error('Requested location id is not accessible.');
	$pines->com_sales->list_countsheets();
	return;
}
$location->com_sales_task_countsheet = true;
if ($location->save()) {
	pines_notice('Countsheet Assigned to ['.$location->name.']');
} else {
	pines_error('Error saving countsheet assignment. Do you have permission?');
}

redirect(pines_url('com_sales', 'listcountsheets'));

?>