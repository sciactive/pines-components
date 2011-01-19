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
		if ($entity->status != 'invoiced' && $entity->status != 'paid') {
			pines_error('Requested sale has not been invoiced.');
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
	// Remove the stock from inventory.
	if ($entity->has_tag('sale')) {
		// Keep track of the whole process.
		$no_errors = true;
		$packing_list = (array) json_decode($_REQUEST['packing_list'], true);
		// Go through each product on the packing list, marking its stock as shipped.
		foreach ($packing_list as $key => $stock_keys) {
			$key = (int) $key;
			if (!isset($entity->products[$key]) || $entity->products[$key]['delivery'] != 'shipped' || !is_array($entity->products[$key]['stock_entities'])) {
				$no_errors = false;
				continue;
			}
			if (!is_array($entity->products[$key]['shipped_entities']))
				$entity->products[$key]['shipped_entities'] = array();
			foreach ($stock_keys as $cur_stock_key) {
				$cur_stock_key = (int) $cur_stock_key;
				if (!isset($entity->products[$key]['stock_entities'][$cur_stock_key]) || $entity->products[$key]['stock_entities'][$cur_stock_key]->in_array($entity->products[$key]['shipped_entities'])) {
					$no_errors = false;
					continue;
				}
				// Remove inventory and save stock entity.
				if ($entity->products[$key]['stock_entities'][$cur_stock_key]->remove('sale_shipped', $entity) && $entity->products[$key]['stock_entities'][$cur_stock_key]->save()) {
					$entity->products[$key]['shipped_entities'][] = $entity->products[$key]['stock_entities'][$cur_stock_key];
				} else {
					$no_errors = false;
				}
			}
		}
		if (!$no_errors)
			pines_notice('Errors occured while removing stock from inventory. Please check that all stock was removed correctly.');
	}
}

// Check all products are shipped.
if (!$entity->warehouse_items || $entity->warehouse_complete) {
	$all_shipped = true;
	foreach ($entity->products as $cur_product) {
		if ($cur_product['delivery'] != 'shipped')
			continue;
		// Calculate included stock entries.
		$stock_entries = $cur_product['stock_entities'];
		$shipped_stock_entries = (array) $cur_product['shipped_entities'];
		foreach ((array) $cur_product['returned_stock_entities'] as $cur_stock_entity) {
			$i = $cur_stock_entity->array_search($stock_entries);
			if (isset($i))
				unset($stock_entries[$i]);
			// If it's still in there, it was entered on the sale twice (fulfilled after returned once), so don't remove it from shipped.
			if (!$cur_stock_entity->in_array($stock_entries)) {
				$i = $cur_stock_entity->array_search($shipped_stock_entries);
				if (isset($i))
					unset($shipped_stock_entries[$i]);
			}
		}
		// If shipped entities is less than stock entities, there are still products to ship.
		if (count($shipped_stock_entries) < count($stock_entries)) {
			$all_shipped = false;
			break;
		}
	}
	if ($all_shipped) {
		// All shipped, so mark the sale.
		$entity->remove_tag('shipping_pending');
		$entity->add_tag('shipping_shipped');
	}
}

if ($entity->save()) {
	pines_notice('Saved shipment ['.$entity->id.']');
} else {
	pines_error('Error saving shipment. Do you have permission?');
}

if ($entity->has_tag('shipping_pending')) {
	redirect(pines_url('com_sales', 'stock/shipments'));
} else {
	redirect(pines_url('com_sales', 'stock/shipments', array('removed' => 'true')));
}

?>