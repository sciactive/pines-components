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
		punt_user('You don\'t have necessary permission.', pines_url('com_sales', 'listcountsheets'));
	$countsheet = com_sales_countsheet::factory((int) $_REQUEST['id']);
	if (is_null($countsheet->guid) || $countsheet->final) {
		pines_error('Requested countsheet id is not accessible.');
		return;
	}
} else {
	if ( !gatekeeper('com_sales/newcountsheet') )
		punt_user('You don\'t have necessary permission.', pines_url('com_sales', 'listcountsheets'));
	$countsheet = com_sales_countsheet::factory();
}

if (is_null($countsheet->creator))
	$countsheet->creator = $_SESSION['user'];
$countsheet->entries = (array) json_decode($_REQUEST['entries']);

$countsheet->comments = $_REQUEST['comments'];

if (empty($countsheet->entries)) {
	pines_notice("No products were counted.");
	$countsheet->print_form();
	return;
}

if ($pines->config->com_sales->global_countsheets)
	$countsheet->ac->other = 1;

if ($_REQUEST['save'] == 'commit') {
	$countsheet->final = true;
	if (isset($_SESSION['user']->task_inventory)) {
		unset($_SESSION['user']->task_inventory);
		$_SESSION['user']->save();
	}
	//Automatically decline the countsheet if they are missing an item.
	$in_stock = array('available', 'unavailable', 'sold_pending');
	array_walk($in_stock, 'preg_quote');
	$regex = '/'.implode('|', $in_stock).'/';
	// Check the countsheet for any missing items.
	$missing = false;
	$expected = $pines->entity_manager->get_entities(array('match' => array('status' => $regex), 'tags' => array('com_sales', 'stock'), 'class' => com_sales_stock));
	foreach ($expected as $key => &$checklist) {
		foreach ($countsheet->entries as $itemkey => $item) {
			if ($checklist->serial == $item->values[0]) {
				unset($expected[$key]);
			} else if ($checklist->product->sku == $item->values[0]) {
				unset($expected[$key]);
			}
		}
		if (isset($expected[$key])) {
			$missing = true;
			break;
		}
	}
	if ($missing)
		$countsheet->status = 'declined';
}
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