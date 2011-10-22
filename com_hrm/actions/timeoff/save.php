<?php
/**
 * Save a request for time off.
 *
 * @package Pines
 * @subpackage com_hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_hrm/clock') )
	punt_user(null, pines_url('com_hrm', 'timeoff/save'));

$pines->page->override = true;
header('Content-Type: application/json');

if (isset($_REQUEST['employee'])) {	
	if ($_REQUEST['start'] != 'Date') {
		$rto_month = date('n', strtotime($_REQUEST['start']));
		$rto_day = date('j', strtotime($_REQUEST['start']));
		$rto_year = date('Y', strtotime($_REQUEST['start']));
		$rto_endmonth = date('n', strtotime($_REQUEST['end']));
		$rto_endday = date('j', strtotime($_REQUEST['end']));
		$rto_endyear = date('Y', strtotime($_REQUEST['end']));
	} else {
		// Default to the current date.
		$rto_month = date('n');
		$rto_day = date('j');
		$rto_year = date('Y');
		$rto_endmonth = date('n');
		$rto_endday = date('j');
		$rto_endyear = date('Y');
	}
	if (!empty($_REQUEST['id'])) {
		$rto = com_hrm_rto::factory((int) $_REQUEST['id']);
		if (!isset($rto->guid)) {
			$pines->page->override_doc('false');
			return;
		}
	} else {
		$rto = com_hrm_rto::factory();
	}

	$rto->employee = com_hrm_employee::factory((int) $_REQUEST['employee']);
	if (!isset($rto->employee->guid)) {
		$pines->page->override_doc('false');
		return;
	}
	$rto->reason = $_REQUEST['reason'];
	$rto->all_day = ($_REQUEST['all_day'] == 'true');
	$rto->start = mktime($rto->all_day ? 0 : $_REQUEST['time_start'],0,0,$rto_month,$rto_day,$rto_year);
	$rto->end = mktime($rto->all_day ? 23 : $_REQUEST['time_end'],$rto->all_day ? 59 : 0,$rto->all_day ? 59 : 0,$rto_endmonth,$rto_endday,$rto_endyear);
	$rto->status = 'pending';

	if ($pines->config->com_hrm->global_rtos)
		$rto->ac->other = 1;

	if ($rto->save()) {
		$pines->page->override_doc('true');
	} else {
		$pines->page->override_doc('false');
	}
}

?>