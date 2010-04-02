<?php
/**
 * Save changes to a countsheeet.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
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
		pines_error('Requested countsheet id is not accessible');
		return;
	}
} else {
	if ( !gatekeeper('com_sales/newcountsheet') )
		punt_user('You don\'t have necessary permission.', pines_url('com_sales', 'listcountsheets', null, false));
	$countsheet = com_sales_countsheet::factory();
}

if (is_null($countsheet->creator))
	$countsheet->creator = $_SESSION['user'];
$countsheet->entries = (array) json_decode($_REQUEST['entries']);

if (empty($countsheet->entries)) {
	pines_notice("No products were counted.");
	$countsheet->print_form();
	return;
}

if ($pines->config->com_sales->global_countsheets)
	$countsheet->ac->other = 1;

if ($_REQUEST['save'] == 'commit')
	$countsheet->final = true;
	
if ($countsheet->save()) {
	if ($countsheet->final) {
		pines_notice('Committed countsheet ['.$countsheet->guid.']');
	} else {
		pines_notice('Saved countsheet ['.$countsheet->guid.']');
	}
} else {
	$countsheet->print_form();
	pines_error('Error saving countsheet. Do you have permission?');
	return;
}

$pines->com_sales->list_countsheets();
?>