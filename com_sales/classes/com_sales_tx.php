<?php
/**
 * com_sales_tx class.
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
 * A transaction.
 *
 * @package Pines
 * @subpackage com_sales
 */
class com_sales_tx extends entity {
	/**
	 * Create a new instance.
	 * @return com_sales_tx The new instance.
	 */
	public static function factory() {
		global $pines;
		$class = get_class();
		$args = func_get_args();
		$entity = new $class($args);
		$entity->add_tag('com_sales', 'transaction');
		$entity->ac = (object) array('user' => 2, 'group' => 2, 'other' => 2);
		$pines->hook->hook_object($entity, $class.'->', false);
		return $entity;
	}
}

?>