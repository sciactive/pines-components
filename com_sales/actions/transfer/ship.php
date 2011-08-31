<?php
/**
 * Provide a form to ship a transfer.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/shipstock') )
	punt_user(null, pines_url('com_sales', 'transfer/ship', array('id' => $_REQUEST['id'])));

$entity = com_sales_transfer::factory((int) $_REQUEST['id']);
if (!isset($entity->guid) || !$entity->final) {
	pines_error('Requested transfer id is not accessible or not committed.');
	return;
}
if ($entity->shipped) {
	pines_notice('This transfer is already shipped.');
	return;
}

$guids = array();
foreach ($entity->products as $cur_product) {
	$stock = $pines->entity_manager->get_entity(
			array('class' => com_sales_stock, 'skip_ac' => true),
			array('&',
				'tag' => array('com_sales', 'stock'),
				'ref' => array(
					array('location', $entity->origin->guid),
					array('product', $cur_product->guid)
				)
			),
			array('!&',
				'guid' => $guids
			)
		);
	if (isset($stock->guid)) {
		$guids[] = $stock->guid;
	} else {
		pines_notice("The product [{$cur_product->name}] is not available in the inventory for {$entity->origin->name}. (Or not enough are available.)");
		pines_redirect(pines_url('com_sales', 'transfer/list'));
		return;
	}
}

$entity->print_ship();

?>