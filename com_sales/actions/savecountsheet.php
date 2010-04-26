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
		punt_user('You don\'t have necessary permission.', pines_url('com_sales', 'listcountsheets'));
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
		punt_user('You don\'t have necessary permission.', pines_url('com_sales', 'listcountsheets'));
	$countsheet = com_sales_countsheet::factory();
}

if (!isset($countsheet->creator))
	$countsheet->creator = $_SESSION['user'];
$countsheet->entries = (array) json_decode($_REQUEST['entries']);
foreach ($countsheet->entries as &$cur_entry) {
	$cur_entry = $cur_entry->values[0];
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
	$countsheet->final = true;
	if (isset($_SESSION['user']->group->com_sales_task_countsheet)) {
		unset($_SESSION['user']->group->com_sales_task_countsheet);
		$_SESSION['user']->group->save();
	}
	//Automatically decline the countsheet if they are missing an item.
	$in_stock = array('available', 'unavailable', 'sold_pending');
	array_walk($in_stock, 'preg_quote');
	$regex = '/'.implode('|', $in_stock).'/';
	// Check the countsheet for any missing items.
	// Get all stock entries at the current location.
	$expected_stock = $pines->entity_manager->get_entities(array('match' => array('status' => $regex), 'ref' => array('location' => $_SESSION['user']->group), 'tags' => array('com_sales', 'stock'), 'class' => com_sales_stock));
	foreach ($expected_stock as &$cur_stock_entry) {
		$found = false;
		foreach ($countsheet->entries as $cur_item) {
			if ((isset($cur_stock_entry->serial) && $cur_stock_entry->serial == $cur_item) || $cur_stock_entry->product->sku == $cur_item)
				$found = true;
		}
		if (!$found) {
			$countsheet->status = 'declined';
			break;
		}
	}
	unset($cur_stock_entry);
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

redirect(pines_url('com_sales', 'listcountsheets'));

?>