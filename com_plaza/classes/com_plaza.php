<?php
/**
 * com_plaza class.
 *
 * @package Pines
 * @subpackage com_plaza
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * com_plaza main class.
 *
 * @package Pines
 * @subpackage com_plaza
 */
class com_plaza extends component {
	/**
	 * Creates and attaches a module which lists packages.
	 */
	function list_packages() {
		global $pines;

		$module = new module('com_plaza', 'package/list', 'content');

		$module->db = $pines->com_package->db;
	}
}

?>