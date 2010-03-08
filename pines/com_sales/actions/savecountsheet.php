<?php
/**
 * Save changes to a countsheeet.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Zak Huber <zakhuber@gmail.com>
 * @copyright Zak Huber
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( isset($_REQUEST['id']) ) {
	if ( !gatekeeper('com_sales/editcountsheet') )
		punt_user('You don\'t have necessary permission.', pines_url('com_sales', 'listcountsheets', null, false));
	$countsheet = com_sales_countsheet::factory((int) $_REQUEST['id']);
	if (is_null($countsheet->guid) || $countsheet->final) {
		display_error('Requested countsheet id is not accessible');
		return;
	}
} else {
	if ( !gatekeeper('com_sales/newcountsheet') )
		punt_user('You don\'t have necessary permission.', pines_url('com_sales', 'listcountsheets', null, false));
	$countsheet = com_sales_countsheet::factory();
}

if (is_null($countsheet->creator))
	$countsheet->creator = $_SESSION['user'];
$countsheet->entries = json_decode($_REQUEST['entries']);
if (!is_array($countsheet->entries))
	$countsheet->entries = array();

if (empty($countsheet->entries)) {
	display_notice("No products were counted.");
	$countsheet->print_form();
	return;
}

if ($pines->config->com_sales->global_countsheets)
	$countsheet->ac->other = 1;

if ($_REQUEST['save'] == 'commit')
	$countsheet->final = true;
	
if ($countsheet->save()) {
	if ($countsheet->final) {
		display_notice('Committed countsheet ['.$countsheet->guid.']');
	} else {
		display_notice('Saved countsheet ['.$countsheet->guid.']');
	}
} else {
	$countsheet->print_form();
	display_error('Error saving countsheet. Do you have permission?');
	return;
}

$pines->com_sales->list_countsheets();
?>