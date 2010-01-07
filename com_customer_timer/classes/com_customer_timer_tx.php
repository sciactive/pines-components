<?php
/**
 * com_customer_timer_tx class.
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
 * A transaction.
 *
 * @package Pines
 * @subpackage com_customer_timer
 */
class com_customer_timer_tx extends entity {
	/**
	 * Create a new instance.
	 */
	public static function factory() {
		global $config;
		$class = get_class();
		$args = func_get_args();
		$entity = new $class($args);
		$config->hook->hook_object($entity, $class.'->', false);
		return $entity;
	}
}

?>