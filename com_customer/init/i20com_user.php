<?php
/**
 * Add hooks for com_user.
 *
 * @package Pines
 * @subpackage com_customer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

/**
 * Shortcut to $pines->com_customer->save_user().
 *
 * This prevents the class from being loaded every script run.
 *
 * @param array &$arguments Arguments.
 * @param string $name Hook name.
 * @param object &$object The user being saved.
 */
function com_customer__save_user(&$arguments, $name, &$object) {
	global $pines;
	return call_user_func(array($pines->com_customer, 'save_user'), $arguments, $name, $object);
}

$pines->hook->add_callback('user->save', -50, 'com_customer__save_user');

?>