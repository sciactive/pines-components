<?php
/**
 * com_package_package class.
 *
 * @package Pines
 * @subpackage com_package
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * A package.
 *
 * @package Pines
 * @subpackage com_package
 */
class com_package_package extends p_base {
	/**
	 * Whether the package is installed.
	 * @var bool
	 * @access private
	 */
	private $installed = false;

	/**
	 * Load a package.
	 */
	public function __construct() {
		// Todo: set up the object.
	}

	/**
	 * Create a new instance.
	 */
	public static function factory() {
		global $pines;
		$class = get_class();
		$args = func_get_args();
		$entity = new $class($args[0]);
		$pines->hook->hook_object($entity, $class.'->', false);
		return $entity;
	}

	/**
	 * Check whether the package is installed.
	 * @return bool True or false.
	 */
	public function is_installed() {
		return $this->installed;
	}
}

?>