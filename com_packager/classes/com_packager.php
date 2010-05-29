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

		$module = new module('com_packager', 'list_packages', 'content');

		$module->packages = $pines->entity_manager->get_entities(array('class' => com_packager_package), array('&', 'tag' => array('com_packager', 'package')));

		if ( empty($module->packages) ) {
			//$module->detach();
			pines_notice('There are no packages.');
		}
	}
}

?>