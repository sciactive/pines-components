<?php
/**
 * com_package class.
 *
 * @package Pines
 * @subpackage com_package
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

/**
 * com_package main class.
 *
 * @package Pines
 * @subpackage com_package
 * @property-read array $db The package database.
 */
class com_package extends component {
	/**
	 * The package database.
	 * @var array
	 * @access private
	 */
	private $_db = array();

	/**
	 * Get a variable.
	 * @param string $name The variable's name.
	 * @return mixed The variable's value.
	 */
	public function __get($name) {
		switch ($name) {
			case 'db':
				if (!$this->_db)
					$this->load_db();
				return $this->_db;
			default:
				return null;
		}
	}

	/**
	 * Set a variable.
	 *
	 * This prevents writing to the $db property.
	 *
	 * @param string $name The variable's name.
	 * @param mixed $value The variable's value.
	 * @return mixed The variable's value.
	 */
	public function __set($name, $value) {
		switch ($name) {
			case 'db':
				return null;
			default:
				return ($this->$name = $value);
		}
	}

	/**
	 * Get the current system package.
	 * @return com_package_package The system package.
	 */
	public function get_system() {
		foreach ($this->db['packages'] as $cur_name => $cur_package) {
			if ($cur_package['type'] == 'system')
				return com_package_package::factory($cur_name);
		}
	}

	/**
	 * Load the package database.
	 * @access private
	 */
	private function load_db() {
		if (!file_exists('components/com_package/includes/cache/db.php'))
			$this->rebuild_db();
		$this->_db = (array) include('components/com_package/includes/cache/db.php');
	}

	/**
	 * Check all the installed packages and build a database.
	 */
	public function rebuild_db() {
		global $pines;
		$db = array(
			'services' => array(),
			'packages' => array()
		);
		// Add the component packages.
		foreach ($pines->all_components as $cur_component) {
			if (substr($cur_component, 0, 4) === 'com_') {
				$dir = is_dir("components/{$cur_component}/") ? "components/{$cur_component}/" : "components/.{$cur_component}/";
			} else {
				$dir = is_dir("templates/{$cur_component}/") ? "templates/{$cur_component}/" : "templates/.{$cur_component}/";
			}
			$db['packages'][$cur_component] = (array) include $dir.'info.php';
			$db['packages'][$cur_component]['package'] = $cur_component;
			$db['packages'][$cur_component]['type'] = substr($cur_component, 0, 4) == 'tpl_' ? 'template' : 'component';
		}
		// Scan the cache for system and metapackage info.
		$cache_files = pines_scandir('components/com_package/includes/cache/');
		$found_system = false;
		// Add the system package and any metapackages.
		foreach ($cache_files as $cur_cache) {
			switch (substr($cur_cache, 0, 4)) {
				case 'sys_':
					// It's a system info file.
					if ($found_system)
						pines_log("Multiple system info files were found in the package cache directory. Please remove the incorrect system info files.", 'warning');
					$found_system = true;
					$name = substr($cur_cache, 4, -4);
					$db['packages'][$name] = (array) include("components/com_package/includes/cache/{$cur_cache}");
					$db['packages'][$name]['package'] = $name;
					$db['packages'][$name]['type'] = 'system';
					break;
				case 'met_':
					// It's a metapackage info file.
					$name = substr($cur_cache, 4, -4);
					$db['packages'][$name] = (array) include("components/com_package/includes/cache/{$cur_cache}");
					$db['packages'][$name]['package'] = $name;
					$db['packages'][$name]['type'] = 'meta';
					break;
			}
		}
		if (!$found_system) {
			// No system package info was found, so let's make one named "pines".
			pines_log("No info file for the system package could be found in the package cache directory. I will attempt to create one called \"sys_pines.php\". If this is a brand new installation, you can ignore this message.", 'warning');
			$system = include('system/info.php');
			$system['package'] = 'pines';
			$system['type'] = 'system';
			file_put_contents('components/com_package/includes/cache/sys_pines.php', "<?php\ndefined('P_RUN') or die('Direct access prohibited');\nreturn ".var_export($system, true).";\n?>");
			$db['packages']['pines'] = $system;
		}
		foreach ($db['packages'] as $cur_package => &$cur_entry) {
			// We don't care about abilities.
			unset($cur_entry['abilities']);
			// Add the services this component provides.
			if ($cur_entry['services']) {
				foreach ($cur_entry['services'] as $cur_service) {
					$db['services'][$cur_service][] = $cur_package;
				}
			}
			// Check dependencies and log any warnings.
			if ($cur_entry['depend']) {
				foreach ($cur_entry['depend'] as $cur_type => $cur_value) {
					if (!$pines->depend->check($cur_type, $cur_value)) {
						pines_notice("The dependency \"{$cur_type}\" of the package \"{$cur_package}\" is not met. The package is probably broken because of this.");
						pines_log("The dependency \"{$cur_type}\" of the package \"{$cur_package}\" is not met. The package is probably broken because of this.", 'warning');
					}
				}
			}
			// Check conflicts and log any warnings.
			if ($cur_entry['conflict']) {
				foreach ($cur_entry['conflict'] as $cur_type => $cur_value) {
					if ($pines->depend->check($cur_type, $cur_value)) {
						pines_notice("The conflict check \"{$cur_type}\" of the package \"{$cur_package}\" is not met. The package is probably broken because of this.");
						pines_log("The conflict check \"{$cur_type}\" of the package \"{$cur_package}\" is not met. The package is probably broken because of this.", 'warning');
					}
				}
			}
		}
		ksort($db);
		// Write the database to a file.
		if (file_put_contents('components/com_package/includes/cache/db.php', "<?php\ndefined('P_RUN') or die('Direct access prohibited');\nreturn ".var_export($db, true).";\n?>")) {
			// Load the new database file.
			$this->load_db();
		} else {
			pines_log('The package database could not be written.', 'error');
		}
	}
}

?>