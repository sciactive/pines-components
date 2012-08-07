<?php
/**
 * Show a shipment's packing list.
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
	punt_user(null, pines_url('com_sales', 'shipment/packinglist', array('id' => $_REQUEST['id'])));

$entity = com_sales_shipment::factory((int) $_REQUEST['id']);
if (!isset($entity->guid)) {
	pines_error('Requested shipment id is not accessible.');
	return;
}

$entity->print_packing_list();

?>