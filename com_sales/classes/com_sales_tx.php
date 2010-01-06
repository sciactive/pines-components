<?php
/**
 * com_sales_tx class.
 *
 * @package Pines
 * @subpackage com_sales
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
 * @subpackage com_sales
 */
class com_sales_tx extends entity {
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