<?php
/**
 * Toggle a shipment's delivered status.
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
	punt_user(null, pines_url('com_sales', 'shipment/delivered', array('id' => $_REQUEST['id'])));

$entity = com_sales_shipment::factory((int) $_REQUEST['id']);
if (!isset($entity->guid)) {
	pines_error('Requested shipment id is not accessible.');
	return;
}

$entity->delivered = !$entity->delivered;

if ($entity->save())
	pines_notice('Shipment saved successfully.');
else
	pines_error('Error saving shipment. Do you have permission?');

pines_redirect(pines_url('com_sales', 'shipment/list'));

?>