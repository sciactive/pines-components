<?php
/**
 * Save changes to a countsheet.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( isset($_REQUEST['id']) ) {
	if ( !gatekeeper('com_sales/editcountsheet') )
		punt_user(null, pines_url('com_sales', 'countsheet/list'));
	$countsheet = com_sales_countsheet::factory((int) $_REQUEST['id']);
	if (!isset($countsheet->guid)) {
		pines_error('Requested countsheet id is not accessible.');
		return;
	}
	if ($countsheet->final) {
		pines_notice('Requested countsheet has been committed.');
		return;
	}
} else {
	if ( !gatekeeper('com_sales/newcountsheet') )
		punt_user(null, pines_url('com_sales', 'countsheet/list'));
	$countsheet = com_sales_countsheet::factory();
}

$countsheet->search_strings = array();
$countsheet->entries = (array) json_decode($_REQUEST['entries']);
foreach ($countsheet->entries as &$cur_entry) {
	$countsheet->search_strings[] = $cur_entry->values[0];
	$cur_entry = (object) array(
		'code' => trim((string) $cur_entry->values[0]),
		'qty' => (int) $cur_entry->values[1]
	);
}
unset($cur_entry);

$countsheet->comments = $_REQUEST['comments'];

if (empty($countsheet->entries)) {
	pines_notice('No products were counted.');
	$countsheet->print_form();
	return;
}

if ($pines->config->com_sales->global_countsheets)
	$countsheet->ac->other = 1;

if ($_REQUEST['save'] == 'commit') {
	// Make sure the countsheet has a group.
	$countsheet->save();
	// Run the count.
	$countsheet->run_count();
	$countsheet->final = true;
	if (isset($_SESSION['user']->group->com_sales_task_countsheet)) {
		unset($_SESSION['user']->group->com_sales_task_countsheet);
		$_SESSION['user']->group->save();
	}
	// Automatically decline the countsheet if it's missing items.
	if ($pines->config->com_sales->decline_countsheets && !empty($countsheet->missing))
		$countsheet->status = 'declined';
}

// Run the count.
$countsheet->run_count();

if ($countsheet->save()) {
	if ($countsheet->final) {
		pines_notice('Committed countsheet ['.$countsheet->guid.']');
	} else {
		pines_notice('Saved countsheet ['.$countsheet->guid.']');
		$countsheet->print_form();
		return;
	}
} else {
	$countsheet->print_form();
	pines_error('Error saving countsheet. Do you have permission?');
	return;
}

redirect(pines_url('com_sales', 'countsheet/list'));

?>