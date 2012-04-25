<?php
/**
 * Swap an item in a sale.
 *
 * @package Components
 * @subpackage sales
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
$swap_item = explode(':', $_REQUEST['swap_item']);
$sku = $swap_item[0];
$old_serial = $swap_item[1];
$new_serial = $_REQUEST['new_serial'];
if (empty($old_serial) || empty($new_serial)){
	pines_notice('Only serialized items may be swapped.');
	pines_redirect(pines_url('com_sales', 'sale/list'));
	return;
}
if ($entity->swap($sku, $old_serial, $new_serial) && $entity->save()) {
	pines_notice("Item [$old_serial] has been swapped with [$new_serial].");
} else {
	pines_notice('The items could not be swapped.');
}

pines_redirect(pines_url('com_sales', 'sale/list'));

?>