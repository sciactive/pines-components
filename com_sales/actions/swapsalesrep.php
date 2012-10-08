<?php
/**
 * Swap salespeople for an item on a sale/return.
 *
 * @package Components\sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/swapsalesrep') )
	punt_user(null, pines_url('com_sales', 'sale/list'));

$pines->page->override = true;
header('Content-Type: application/json');

if ($_REQUEST['type'] == 'sale') {
	$entity = com_sales_sale::factory((int) $_REQUEST['id']);
} elseif ($_REQUEST['type'] == 'return') {
	$entity = com_sales_return::factory((int) $_REQUEST['id']);
}
if (!isset($entity->guid)) {
	$pines->page->override_doc(json_encode('false'));
	return;
}

//$key = intval($_REQUEST['swap_item']);

// Changing this key to a foreach ...
$swap_items = json_decode($_REQUEST['swap_items']);
$new_swap_items = array();
foreach ($swap_items as $swap_item) {
	$new_swap_items[] = $swap_item->value;
}

$new_salesrep = user::factory(intval($_REQUEST['salesperson']));
if (!isset($new_salesrep->guid)) {
	pines_notice('Please check your salespeople for this swap.');
	$pines->page->override_doc('false');
	return;
}
$num_items = count($new_swap_items);
$success = 0;

foreach ($new_swap_items as $swap_item) {
	$key = intval($swap_item);
	if ($entity->swap_salesrep($key, $new_salesrep) && $entity->save())
		$success++;
}

if ($success > 0) {
	if ($num_items > 1)
		pines_notice("{$success} of the {$num_items} were swapped from {$old_salesrep->name} [{$old_salesrep->username}] to {$new_salesrep->name} [{$new_salesrep->username}].");
	else
		pines_notice("The item has been swapped from {$old_salesrep->name} [{$old_salesrep->username}] to {$new_salesrep->name} [{$new_salesrep->username}].");
	$pines->page->override_doc('true');
} else {
	if ($num_items > 1)
		pines_notice('None of the items could be swapped to this salesperson.');
	else
		pines_notice('The salesperson for this item could not be swapped.');
	$pines->page->override_doc('false');	
}

//if ($entity->swap_salesrep($key, $new_salesrep) && $entity->save()) {
//	pines_notice("The item has been swapped from {$old_salesrep->name} [{$old_salesrep->username}] to {$new_salesrep->name} [{$new_salesrep->username}].");
//	$pines->page->override_doc('true');
//} else {
//	pines_notice('The salesperson for this item could not be swapped.');
//	$pines->page->override_doc('false');
//}

?>