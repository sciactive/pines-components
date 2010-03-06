<?php
/**
 * Prevent deleting a logged in customer.
 *
 * @package Pines
 * @subpackage com_customer_timer
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * Deny customers who are logged in from being deleted.
 *
 * @param array $arguments Arguments.
 * @param string $name Hook name.
 * @param object $object The customer being deleted.
 * @return array|bool An array of an entity, or false if the customer is logged in.
 */
function com_customer_timer__check_delete($arguments, $name, $object) {
	if (!is_object($object))
		return $arguments;
	$logins = com_customer_timer_login_tracker::factory();
	if ($logins->logged_in($object)) {
		display_notice("{$object->guid}: {$object->name} is currently logged in to the customer timer and cannot be deleted until logged out.");
		return false;
	}
	return $arguments;
}

$pines->hook->add_callback('com_customer_customer->delete', -10, 'com_customer_timer__check_delete');

?>