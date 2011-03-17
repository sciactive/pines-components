<?php
/**
 * List warehouse items that can be fulfilled / Print fulfillment form.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/warehouse') )
	punt_user(null, pines_url('com_sales', 'warehouse/fulfill', array('id' => $_REQUEST['id'])));

if (!empty($_REQUEST['id'])) {
	$entity = com_sales_sale::factory((int) $_REQUEST['id']);
	if (!isset($entity->guid)) {
		pines_notice('The given ID could not be found.');
		redirect(pines_url('com_sales', 'warehouse/fulfill'));
		return;
	}
	$entity->print_warehouse();
} else {
	$pines->com_sales->warehouse_fulfill();
}

?>