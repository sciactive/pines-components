<?php
/**
 * com_packager class.
 *
 * @package Pines
 * @subpackage com_packager
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * com_packager main class.
 *
 * @package Pines
 * @subpackage com_packager
 */
class com_packager extends component {
	/**
	 * Creates and attaches a module which lists packages.
	 */
	function list_packages() {
		global $pines;

		$pines->com_pgrid->load();

		$module = new module('com_packager', 'list_packages', 'content');
		if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
			$module->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_packager/list_packages'];

		$module->packages = $pines->entity_manager->get_entities(array('tags' => array('com_packager', 'package'), 'class' => com_packager_package));

		if ( empty($module->packages) ) {
			//$module->detach();
			pines_notice('There are no packages.');
		}
	}
}

?>