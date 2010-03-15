<?php
/**
 * com_configure class.
 *
 * @package Pines
 * @subpackage com_configure
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * com_configure main class.
 *
 * Manages Pines configuration.
 *
 * @package Pines
 * @subpackage com_configure
 */
class com_configure extends component {
	/**
	 * An array of config files found on the system.
	 *
	 * Each key is the name of the component to which the config file in its
	 * value belongs.
	 *
	 * @var array $config_files
	 */
	var $config_files = array();

	/**
	 * Fills the $config_files array.
	 */
	function __construct() {
		global $pines;
		$this->config_files['system'] = array('defaults' => 'system/defaults.php', 'config' => 'system/config.php');
		foreach ($pines->all_components as $cur_component) {
			$cur_dir = (substr($cur_component, 0, 4) != 'tpl_') ? 'components' : 'templates';
			if (in_array($cur_component, $pines->components)) {
				$cur_config_file = array('defaults' => "$cur_dir/$cur_component/defaults.php", 'config' => "$cur_dir/$cur_component/config.php");
			} else {
				$cur_config_file = array('defaults' => "$cur_dir/.$cur_component/defaults.php", 'config' => "$cur_dir/.$cur_component/config.php");
			}
			if (file_exists($cur_config_file['defaults']))
				$this->config_files[$cur_component] = $cur_config_file;
		}
	}

	/**
	 * Disables a component.
	 *
	 * This function renames the component's directory by adding a dot (.) in
	 * front of the name. This causes Pines to ignore the component.
	 *
	 * @param string $component The name of the component.
	 * @return bool True on success, false on failure.
	 */
	function disable_component($component) {
		global $pines;
		if (!in_array($component, $pines->all_components)) {
			pines_log("Failed to disable component $component. Component isn't installed", 'error');
			return false;
		}
		$cur_dir = (substr($component, 0, 4) != 'tpl_') ? 'components' : 'templates';
		if (in_array($component, $pines->components) && rename("$cur_dir/$component", "$cur_dir/.$component")) {
			pines_log("Disabled component $component.", 'notice');
			return true;
		} else {
			pines_log("Failed to disable component $component.", 'error');
			return false;
		}
	}

	/**
	 * Enables a component.
	 *
	 * This function renames the component's directory by removing the dot (.)
	 * in front of the name. This causes Pines to recognize the component.
	 *
	 * @param string $component The name of the component.
	 * @return bool True on success, false on failure.
	 */
	function enable_component($component) {
		global $pines;
		if (!in_array($component, $pines->all_components)) {
			pines_log("Failed to enable component $component. Component isn't installed", 'error');
			return false;
		}
		$cur_dir = (substr($component, 0, 4) != 'tpl_') ? 'components' : 'templates';
		if (!in_array($component, $pines->components) && rename("$cur_dir/.$component", "$cur_dir/$component")) {
			pines_log("Enabled component $component.", 'notice');
			return true;
		} else {
			pines_log("Failed to enable component $component.", 'error');
			return false;
		}
	}

	/**
	 * Creates and attaches a module which lists configurable components.
	 */
	function list_components() {
		global $pines;
		$module = new module('com_configure', 'list', 'content');

		$module->components = array_merge(array('system'), $pines->all_components);
		$module->config_components = array_keys($this->config_files);
		$module->disabled_components = array_diff($pines->all_components, $pines->components);
	}

	/**
	 * Creates and attaches a module which lists per user config components.
	 *
	 * @todo Create a view for per user components.
	 */
	function list_components_peruser() {
		global $pines;
		$module = new module('com_configure', 'list', 'content');

		$module->components = array_merge(array('system'), $pines->all_components);
		$module->config_components = array_keys($this->config_files);
		$module->disabled_components = array_diff($pines->all_components, $pines->components);
	}
}

?>