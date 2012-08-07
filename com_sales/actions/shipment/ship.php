<?php
/**
 * Provide a form to edit a shipment.
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

$entity = com_sales_shipment::factory((int) $_REQUEST['id']);
if (!$entity->guid) {
	$entity = com_sales_shipment::factory();
	$sale = com_sales_sale::factory((int) $_REQUEST['id']);
	if ($sale->guid) {
		if (!$entity->load_sale($sale)) {
			pines_error('The sale could not be loaded.');
			return;
		}
		if ($sale->warehouse_pending)
			pines_notice('There are still unassigned warehouse items on this sale. It can only be partially shipped.');
	} else {
		$transfer = com_sales_transfer::factory((int) $_REQUEST['id']);
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

$entity->print_form();

?>