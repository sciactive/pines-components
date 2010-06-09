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
	punt_user('You don\'t have necessary permission.', pines_url('com_sales', 'cashcount/assign', array('location' => $_REQUEST['location'])));

$type = $_REQUEST['count_type'];
$location = group::factory((int) $_REQUEST['location']);
if (!isset($location->guid))
	$location = null;

if (!isset($location)) {
	pines_error('Requested location id is not accessible.');
	$pines->com_sales->list_cashcounts();
	return;
}

switch ($type) {
	case 'cash_audit':
		$location->com_sales_task_cashcount_audit = true;
		$success_msg = 'Cash Audit';
		break;
	case 'cash_deposit':
		$location->com_sales_task_cashcount_deposit = true;
		$success_msg = 'Cash Deposit';
		break;
	case 'cash_skim':
		$location->com_sales_task_cashcount_skim = true;
		$success_msg = 'Cash Skim';
		break;
	case 'cash_count':
	default:
		$location->com_sales_task_cashcount = true;
		$success_msg = 'Cash Count';
		break;
}

if ($location->save()) {
	pines_notice($success_msg.' Assigned to ['.$location->name.']');
} else {
	pines_error('Error saving cash count assignment. Do you have permission?');
}

redirect(pines_url('com_sales', 'cashcount/list'));

?>