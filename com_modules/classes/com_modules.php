<?php
/**
 * com_modules class.
 *
 * @package Components
 * @subpackage modules
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

/**
 * com_modules main class.
 *
 * A module manager. It allows placement of various modules in different
 * positions around the page.
 *
 * @package Components
 * @subpackage modules
 */
class com_modules extends component {
	/**
	 * Creates and attaches a module which lists modules.
	 * @return module The module.
	 */
	public function list_modules() {
		global $pines;

		$module = new module('com_modules', 'module/list', 'content');

		$module->modules = $pines->entity_manager->get_entities(array('class' => com_modules_module), array('&', 'tag' => array('com_modules', 'module')));

		if ( empty($module->modules) )
			pines_notice('There are no modules.');

		return $module;
	}

	/**
	 * Get an array of all the module types.
	 * 
	 * Goes through each component's modules.php file.
	 *
	 * @return array Module types.
	 */
	public function module_types() {
		global $pines;
		$return = array();
		foreach ($pines->components as $cur_component) {
			if (strpos($cur_component, 'tpl_') === 0)
				continue;
			if (!file_exists("components/$cur_component/modules.php"))
				continue;
			$modules = include("components/$cur_component/modules.php");
			if (!$modules || (array) $modules !== $modules)
				continue;
			foreach ($modules as $key => $cur_module) {
				if (isset($cur_module['type']) && !preg_match('/\bmodule\b/', $cur_module['type']))
					unset($modules[$key]);
			}
			if ($modules)
				$return[$cur_component] = $modules;
		}
		return $return;
	}
}

?>