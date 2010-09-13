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
		break;
}

$entity->shipper = com_sales_shipper::factory((int) $_REQUEST['shipper']);
if (!isset($entity->shipper->guid))
	$entity->shipper = null;
$entity->eta = strtotime($_REQUEST['eta']);
$entity->tracking_numbers = array_diff(array_map('trim', (array) explode("\n", $_REQUEST['tracking_numbers'])), array(''));
if ($_REQUEST['shipped'] == 'ON') {
	$entity->remove_tag('shipping_pending');
	$entity->add_tag('shipping_shipped');
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