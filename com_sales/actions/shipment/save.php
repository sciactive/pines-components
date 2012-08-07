<?php
/**
 * Save changes to a shipment.
 *
 * @package Components\sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/managestock') )
	punt_user(null, pines_url('com_sales', 'shipment/ship', array('id' => $_REQUEST['id'])));

if (isset($_REQUEST['id'])) {
	$entity = com_sales_shipment::factory((int) $_REQUEST['id']);
	if (!isset($entity->guid)) {
		pines_error('Requested shipment id is not accessible.');
		return;
	}
} else {
	$entity = com_sales_shipment::factory();

	$sale = com_sales_sale::factory((int) $_REQUEST['ref_id']);
	if ($sale->guid) {
		if (!$entity->load_sale($sale)) {
			pines_error('The sale could not be loaded.');
			return;
		}
	} else {
		$transfer = com_sales_transfer::factory((int) $_REQUEST['ref_id']);
		if (!isset($transfer->guid)) {
			pines_error('Requested id is not accessible.');
			return;
		}
		$entity = com_sales_shipment::factory();
		if (!$entity->load_transfer($transfer)) {
			pines_error('The transfer could not be loaded.');
			return;
		}
	}
}

$entity->shipper = com_sales_shipper::factory((int) $_REQUEST['shipper']);
if (!isset($entity->shipper->guid))
	$entity->shipper = null;
$entity->eta = strtotime($_REQUEST['eta']);
$entity->tracking_numbers = array_diff(array_map('trim', (array) explode("\n", trim($_REQUEST['tracking_numbers']))), array(''));

if (!$entity->shipped) {
	// Take out the products that weren't submitted.
	$packing_list = (array) json_decode($_REQUEST['packing_list'], true);
	foreach ($entity->products as $key => $cur_product) {
		if (!$packing_list[$key]) {
			unset($entity->products[$key]);
			continue;
		}
		foreach ($cur_product['stock_entities'] as $cur_key => $cur_stock) {
			if (!in_array("$cur_stock->guid", $packing_list[$key]))
				unset($entity->products[$key]['stock_entities'][$cur_key]);
		}
	}
}

$entity->notes = $_REQUEST['notes'];

if ($entity->save()) {
	pines_notice('Saved shipment ['.$entity->id.']');
	if (!$entity->shipped) {
		if (!$entity->remove_stock())
			pines_notice('Errors occured while removing stock from inventory. Please check that all stock was removed correctly.');
		if (!$entity->save())
			pines_error('Couldn\'t save shipment after removing inventory. It may show as not shipped until you try again.');
	}
} else {
	pines_error('Error saving shipment. Do you have permission?');
}

pines_redirect(pines_url('com_sales', 'shipment/packinglist', array('id' => $entity->guid)));

?>