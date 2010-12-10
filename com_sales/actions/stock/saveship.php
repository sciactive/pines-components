<?php
/**
 * Save changes to a shipment.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/managestock') )
	punt_user(null, pines_url('com_sales', 'stock/ship', array('id' => $_REQUEST['id'])));

switch (strtolower($_REQUEST['type'])) {
	case 'sale':
	default:
		$type = 'sale';
		$entity = com_sales_sale::factory((int) $_REQUEST['id']);
		if (!isset($entity->guid)) {
			pines_error('Requested sale id is not accessible.');
			return;
		}
		if ($entity->warehouse_items && !$entity->warehouse_complete) {
			pines_notice('Requested sale has unfulfilled warehouse items.');
			return;
		}
		break;
}

$entity->shipper = com_sales_shipper::factory((int) $_REQUEST['shipper']);
if (!isset($entity->shipper->guid))
	$entity->shipper = null;
$entity->eta = strtotime($_REQUEST['eta']);
$entity->tracking_numbers = array_diff(array_map('trim', (array) explode("\n", trim($_REQUEST['tracking_numbers']))), array(''));
if ($_REQUEST['shipped'] == 'ON') {
	$entity->remove_tag('shipping_pending');
	$entity->add_tag('shipping_shipped');
	// Remove the stock from inventory.
	if ($entity->has_tag('sale')) {
		// Keep track of the whole process.
		$no_errors = true;
		// Go through each product, marking its stock as shipped.
		foreach ($entity->products as &$cur_product) {
			if ($cur_product['delivery'] != 'shipped' || !is_array($cur_product['stock_entities']))
				continue;
			// Remove stock from inventory.
			foreach ($cur_product['stock_entities'] as &$cur_stock) {
				$no_errors = $cur_stock->remove('sale_shipped', $entity) && $cur_stock->save() && $no_errors;
			}
		}
		unset($cur_product);
		if (!$no_errors)
			pines_notice('Errors occured while removing stock from inventory. Please check that all stock was removed correctly.');
	}
} else {
	$entity->add_tag('shipping_pending');
	$entity->remove_tag('shipping_shipped');
}

if ($entity->save()) {
	pines_notice('Saved shipment ['.$entity->guid.']');
} else {
	pines_error('Error saving shipment. Do you have permission?');
}

if ($entity->has_tag('shipping_pending')) {
	redirect(pines_url('com_sales', 'stock/shipments'));
} else {
	redirect(pines_url('com_sales', 'stock/shipments', array('removed' => 'true')));
}

?>