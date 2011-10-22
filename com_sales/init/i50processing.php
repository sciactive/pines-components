<?php
/**
 * Add the default processing types.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

/**
 * Shortcut to $pines->com_sales->payment_instant().
 *
 * This prevents the class from being loaded every script run.
 *
 * @param array &$array The argument array.
 * @return mixed The method's return value.
 */
function com_sales__payment_instant(&$array) {
	global $pines;
	return call_user_func(array($pines->com_sales, 'payment_instant'), $array);
}
/**
 * Shortcut to $pines->com_sales->payment_manager().
 *
 * This prevents the class from being loaded every script run.
 *
 * @param array &$array The argument array.
 * @return mixed The method's return value.
 */
function com_sales__payment_manager(&$array) {
	global $pines;
	return call_user_func(array($pines->com_sales, 'payment_manager'), $array);
}

$pines->config->com_sales->processing_types[] = array(
	'name' => 'com_sales/instant',
	'cname' => 'Instant Processing',
	'description' => 'Approval and processing happen immediately. For example, a cash transaction.',
	'callback' => 'com_sales__payment_instant'
);
$pines->config->com_sales->processing_types[] = array(
	'name' => 'com_sales/manager',
	'cname' => 'Manager Approval',
	'description' => 'Approval happens only after a manager verifies the payment. For example, a large cash transaction.',
	'callback' => 'com_sales__payment_manager'
);

?>