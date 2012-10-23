<?php
/**
 * Swap an item in a sale.
 *
 * @package Components\sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/swapsale') )
	punt_user(null, pines_url('com_sales', 'sale/swap', array('id' => $_REQUEST['id'], 'swap_item' => $_REQUEST['swap_item'], 'serial' => $_REQUEST['serial'])));

$entity = com_sales_sale::factory((int) $_REQUEST['id']);

if (!isset($entity->guid)) {
	pines_notice('The given ID could not be found.');
	pines_redirect(pines_url('com_sales', 'sale/list'));
	return;
}
list($key, $stock_guid) = explode('_', $_REQUEST['swap_item']);
$old_item = com_sales_stock::factory((int) $stock_guid);
if (!isset($old_item->guid)){
	pines_notice('Current item not found.');
	pines_redirect(pines_url('com_sales', 'sale/list'));
	return;
}
$item_action = $_REQUEST['item_action'];
if ($item_action == 'swap') {
	$new_item = com_sales_stock::factory((int) $_REQUEST['new_item']);
	if (!isset($new_item->guid)){
		pines_notice('New item not found.');
		pines_redirect(pines_url('com_sales', 'sale/list'));
		return;
	}
	if ($entity->swap($key, $old_item, $new_item))
		pines_notice('Item has been swapped successfully.');
	else
		pines_notice('The items could not be swapped.');
} elseif ($item_action == 'remove') {
	if ($entity->remove_item($key, $old_item))
		pines_notice('Item has been removed successfully.');
	else
		pines_notice('The item could not be removed.');
}

pines_redirect(pines_url('com_sales', 'sale/list'));

?>