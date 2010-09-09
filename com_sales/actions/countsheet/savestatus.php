<?php
/**
 * Save approval changes to a countsheet.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/approvecountsheet') )
	punt_user(null, pines_url('com_sales', 'countsheet/approve'));

$countsheet = com_sales_countsheet::factory((int) $_REQUEST['id']);
if (!isset($countsheet->guid)) {
	pines_error('Requested countsheet id is not accessible.');
	return;
}

$countsheet->status = $_REQUEST['status'];
$countsheet->review_comments = $_REQUEST['review_comments'];

if (!$countsheet->final && $countsheet->status == 'approved') {
	pines_notice('You cannot approve a countsheet until it has been committed.');
	$countsheet->print_review();
	return;
}

if ($countsheet->save()) {
	switch ($countsheet->status) {
		case 'approved':
			pines_notice('Approved countsheet ['.$countsheet->guid.']');
			break;
		case 'declined':
			pines_notice('Declined countsheet ['.$countsheet->guid.']');
			break;
		default:
			pines_notice('Saved countsheet ['.$countsheet->guid.']');
			break;
	}
} else {
	pines_error('Error saving countsheet. Do you have permission?');
}

redirect(pines_url('com_sales', 'countsheet/list'));

?>