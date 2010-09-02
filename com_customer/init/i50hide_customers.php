<?php
/**
 * Hide customers when viewing user list.
 *
 * @package Pines
 * @subpackage com_customer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if (!$pines->config->com_customer->hide_customers)
	return;

/**
 * Add hook to hide customers.
 */
function com_customer__hook_entities() {
	global $pines;
	$pines->info->com_customer->hook_callbacks = $pines->hook->add_callback('$pines->entity_manager->get_entities', -10, 'com_customer__hide_customers');
}
/**
 * Hide customers from com_user.
 *
 * @param array &$args The arguments.
 */
function com_customer__hide_customers(&$args) {
	global $pines;
	if ($args[0]['class'] == user) {
		$args[] = array('!&',
				'tag' => array('com_customer', 'customer')
			);
		$pines->hook->del_callback_by_id('$pines->entity_manager->get_entities', $pines->info->com_customer->hook_callbacks[0]);
	}
}

$pines->hook->add_callback('$pines->user_manager->list_users', -10, 'com_customer__hook_entities');

?>