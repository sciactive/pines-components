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
		if (file_exists('system/configure.php'))
			$this->config_files['system'] = 'system/configure.php';
		foreach ($pines->all_components as $cur_component) {
			$cur_dir = (substr($cur_component, 0, 4) != 'tpl_') ? 'components' : 'templates';
			if (in_array($cur_component, $pines->components)) {
				$cur_config_file = "$cur_dir/$cur_component/configure.php";
			} else {
				$cur_config_file = "$cur_dir/.$cur_component/configure.php";
			}
			if (file_exists($cur_config_file))
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
	 * Parse a Pines configuration file.
	 *
	 * @param string $config_file The config file to read.
	 * @return array|bool The array of configuration variables on success, false on failure.
	 */
	function get_config_array($config_file) {
		global $pines;
		if (!file_exists($config_file)) return false;
		$config_array = include($config_file);
		if (!is_array($config_array)) return false;
		return $config_array;
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

		//This shouldn't even be possible, but just in case...
		if ( empty($module->components) ) {
			$module->detach();
			display_notice("There are no installed components.");
		}
	}

	/**
	 * Write config array into an existing Pines configuration file.
	 *
	 * @param config $config_array The array to write.
	 * @param string $config_file The config file to modify.
	 * @return bool True on success, false on failure.
	 */
	function put_config_array($config_array, $config_file) {
		if (!file_exists($config_file))
			return false;
		if (!($file_contents = file_get_contents($config_file)))
			return false;
		$pattern = '/(return\s*\(?\s*)\S.*(\)?\s*;)/s';
		$replacement = '$1#CODEGOESHERE#$2';
		/* simplified pattern, but it replaces parenthesis...
		$pattern = '/return(\s|[(]).*;/s';
		$replacement = 'return #CODEGOESHERE#;'; */
		$file_contents = preg_replace($pattern, $replacement, $file_contents, 1);
		$file_contents = str_replace('#CODEGOESHERE#', 'unserialize(stripslashes("'.addslashes(serialize($config_array)).'"))', $file_contents);
		if (!(file_put_contents($config_file, $file_contents)))
			return false;
		pines_log("Saved new config to $config_file.", 'notice');
		return true;
	}
}

?>