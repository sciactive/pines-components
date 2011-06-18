<?php
/**
 * Swap an item on an ESP.
 *
 * @package Pines
 * @subpackage com_esp
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_esp/editplan') )
	punt_user(null, pines_url('com_esp', 'swap', array('id' => $_REQUEST['id'], 'swap_item' => $_REQUEST['swap_item'], 'serial' => $_REQUEST['serial'])));

$entity = com_esp_plan::factory((int) $_REQUEST['id']);

if (!isset($entity->guid)) {
	pines_notice('The given ID could not be found.');
	pines_redirect(pines_url('com_sales', 'sale/list'));
	return;
}
$old_serial = $entity->item['serial'];
$new_serial = $_REQUEST['new_serial'];
if (empty($new_serial)){
	pines_notice('Only serialized items may be swapped.');
	pines_redirect(pines_url('com_esp', 'list'));
	return;
}
if ($entity->swap($new_serial) && $entity->save()) {
	pines_notice("Item [$old_serial] has been swapped with [$new_serial].");
} else {
	pines_notice('The items could not be swapped.');
}

pines_redirect(pines_url('com_esp', 'list'));

?>