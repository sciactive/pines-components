<?php
/**
 * com_customertimer_tx class.
 *
 * @package Pines
 * @subpackage com_customertimer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * A transaction.
 *
 * @package Pines
 * @subpackage com_customertimer
 */
class com_customertimer_tx extends entity {
	/**
	 * Create a new instance.
	 */
	public static function factory() {
		global $pines;
		$class = get_class();
		$args = func_get_args();
		$entity = new $class($args);
		$pines->hook->hook_object($entity, $class.'->', false);
		return $entity;
	}
}

?>