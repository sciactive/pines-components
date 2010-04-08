<?php
/**
 * Save approval changes to a countsheet.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zakhuber@gmail.com>
 * @copyright Zak Huber
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/approvecountsheet') )
	punt_user('You don\'t have necessary permission.', pines_url('com_sales', 'approvecountsheet'));

if (is_null($_REQUEST['id'])) {
	pines_notice('No countsheet ID provided.');
	return;
}

// Retrieve the desired countsheet.
$countsheet = com_sales_countsheet::factory((int) $_REQUEST['id']);

if (is_null($countsheet->guid)) {
	pines_error('Requested countsheet id is not accessible');
	return;
}

$countsheet->status = $_REQUEST['status'];
$countsheet->review_comments = $_REQUEST['review_comments'];

if (!$countsheet->final && $countsheet->status == 'approved') {
	pines_error('You cannot approve a countsheet until it has been committed.');
	$pines->com_sales->list_countsheets();
	return;
}

if ($countsheet->save()) {
	if ($countsheet->status == 'approved') {
		pines_notice('Approved countsheet ['.$countsheet->guid.']');
	} else if ($countsheet->status == 'declined') {
		pines_notice('Declined countsheet ['.$countsheet->guid.']');
	} else {
		pines_notice('Saved countsheet ['.$countsheet->guid.']');
	}
} else {
	pines_error('Error saving countsheet. Do you have permission?');
}

$pines->com_sales->list_countsheets();
?>