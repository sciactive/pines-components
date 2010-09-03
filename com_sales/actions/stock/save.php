<?php
/**
 * Save changes to a stock entry.
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
	punt_user('You don\'t have necessary permission.', pines_url('com_sales', 'stock/list'));
$list = array_map('intval', explode(',', $_REQUEST['id']));

if (empty($list)) {
	pines_notice('No inventory specified!');
	return;
}

$entities = (array) $pines->entity_manager->get_entities(
		array('class' => com_sales_stock),
		array('&',
			'tag' => array('com_sales', 'stock')
		),
		array('|',
			'guid' => $list
		)
	);
$available = ($_REQUEST['available'] == 'ON');
$serial = ($_REQUEST['serial'] == '') ? null : $_REQUEST['serial'];
$location = ($_REQUEST['location_null'] == 'ON') ? null : group::factory((int) $_REQUEST['location']);
$vendor = ($_REQUEST['vendor'] == 'null') ? null : com_sales_vendor::factory((int) $_REQUEST['vendor']);
$cost = ($_REQUEST['cost'] == '') ? null : (float) $_REQUEST['cost'];
$count = count($entities);
foreach ($entities as $stock) {
	if (!isset($stock->guid)) {
		pines_error('Requested stock id is not accessible.');
		continue;
	}

	if ($_REQUEST['available_change'] == '1' && $stock->available != $available) {
		if (!in_array(
				$_REQUEST['available_reason'],
				array(
					'unavailable_on_hold',
					'unavailable_damaged',
					'unavailable_destroyed',
					'unavailable_missing',
					'unavailable_theft',
					'unavailable_display',
					'unavailable_promotion',
					'unavailable_gift',
					'unavailable_error_sale',
					'unavailable_error_return',
					'unavailable_error_po',
					'unavailable_error_transfer',
					'unavailable_error_adjustment',
					'unavailable_error',
					'available_not_on_hold',
					'available_repaired',
					'available_not_destroyed',
					'available_found',
					'available_recovered',
					'available_not_display',
					'available_not_promotion',
					'available_not_gift',
					'available_error_sale',
					'available_error_return',
					'available_error_po',
					'available_error_transfer',
					'available_error_adjustment',
					'available_error'
				)
			)) {
			pines_notice('Invalid availability reason submitted.');
		} else {
			$tx = com_sales_tx::factory('stock_tx');
			$old_available = $stock->available;
			$stock->available = $available;
			$tx->type = 'adjusted';
			$tx->property = 'available';
			$tx->reason = $_REQUEST['available_reason'];
			$tx->old_available = $old_available;
			$tx->new_available = $stock->available;
			$tx->stock = $stock;
			$tx->save();
		}
	}
	if ($count == 1 && $_REQUEST['serial_change'] == '1' && $stock->serial != $serial) {
		if (!in_array(
				$_REQUEST['serial_reason'],
				array(
					'serial_changed_reserialize',
					'serial_changed_damaged',
					'serial_changed_error_po',
					'serial_changed_error_adjustment',
					'serial_changed_error'
				)
			)) {
			pines_notice('Invalid serial reason submitted.');
		} else {
			$tx = com_sales_tx::factory('stock_tx');
			$old_serial = $stock->serial;
			$stock->serial = $serial;
			$tx->type = 'adjusted';
			$tx->property = 'serial';
			$tx->reason = $_REQUEST['serial_reason'];
			$tx->old_serial = $old_serial;
			$tx->new_serial = $stock->serial;
			$tx->stock = $stock;
			$tx->save();
		}
	}
	if ($_REQUEST['location_change'] == '1' && $stock->location->guid != $location->guid) {
		if (!in_array(
				$_REQUEST['location_reason'],
				array(
					'location_changed_shipped',
					'location_changed_picked_up',
					'location_changed_trashed',
					'location_changed_missing',
					'location_changed_theft',
					'location_changed_not_trashed',
					'location_changed_found',
					'location_changed_recovered',
					'location_changed_promotion',
					'location_changed_gift',
					'location_changed_error_sale',
					'location_changed_error_return',
					'location_changed_error_po',
					'location_changed_error_transfer',
					'location_changed_error_adjustment',
					'location_changed_error'
				)
			)) {
			pines_notice('Invalid location reason submitted.');
		} else {
			$tx = com_sales_tx::factory('stock_tx');
			$old_location = $stock->location;
			$stock->location = $location;
			$tx->type = 'adjusted';
			$tx->property = 'location';
			$tx->reason = $_REQUEST['location_reason'];
			$tx->old_location = $old_location;
			$tx->new_location = $stock->location;
			$tx->stock = $stock;
			$tx->save();
		}
	}
	if ($_REQUEST['vendor_change'] == '1' && $stock->vendor->guid != $vendor->guid) {
		if (!in_array(
				$_REQUEST['vendor_reason'],
				array(
					'vendor_changed_error_po',
					'vendor_changed_error_adjustment',
					'vendor_changed_error'
				)
			)) {
			pines_notice('Invalid vendor reason submitted.');
		} else {
			$tx = com_sales_tx::factory('stock_tx');
			$old_vendor = $stock->vendor;
			$stock->vendor = $vendor;
			$tx->type = 'adjusted';
			$tx->property = 'vendor';
			$tx->reason = $_REQUEST['vendor_reason'];
			$tx->old_vendor = $old_vendor;
			$tx->new_vendor = $stock->vendor;
			$tx->stock = $stock;
			$tx->save();
		}
	}
	if ($_REQUEST['cost_change'] == '1' && $stock->cost != $cost) {
		if (!in_array(
				$_REQUEST['cost_reason'],
				array(
					'cost_changed_error_po',
					'cost_changed_error_adjustment',
					'cost_changed_error'
				)
			)) {
			pines_notice('Invalid cost reason submitted.');
		} else {
			$tx = com_sales_tx::factory('stock_tx');
			$old_cost = $stock->cost;
			$stock->cost = $cost;
			$tx->type = 'adjusted';
			$tx->property = 'cost';
			$tx->reason = $_REQUEST['cost_reason'];
			$tx->old_cost = $old_cost;
			$tx->new_cost = $stock->cost;
			$tx->stock = $stock;
			$tx->save();
		}
	}

	if ($stock->save()) {
		pines_notice('Saved stock entry ['.$stock->guid.']');
	} else {
		pines_error('Error saving stock entry. Do you have permission?');
	}
}

redirect(pines_url('com_sales', 'stock/list'));

?>