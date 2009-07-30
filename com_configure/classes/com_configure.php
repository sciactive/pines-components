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
	var $config_files = array();

	function __construct() {
		global $config;
		if (file_exists('configure.php'))
			$this->config_files['system'] = 'configure.php';
		foreach ($config->components as $cur_component) {
			$cur_config_file = 'components/'.$cur_component.'/configure.php';
			if (file_exists($cur_config_file))
				$this->config_files[$cur_component] = $cur_config_file;
		}
	}

    /**
     * Parse a Pines WDDX configuration file.
     *
     * @param string $config_file The config file to read.
     * @return array|bool The array of configuration variables on success, false on failure.
     */
    function get_wddx_array($config_file) {
        if (!file_exists($config_file)) return false;
        $wddx_data = include($config_file);
        return wddx_deserialize($wddx_data);
    }

    /**
     * Extract the WDDX data from a Pines WDDX configuration file.
     *
     * @param string $config_file The config file to read.
     * @return string|bool The WDDX data on success, false on failure.
     */
    function get_wddx_data($config_file) {
        if (!file_exists($config_file)) return false;
        return include($config_file);
    }

    /**
     * Creates and attaches a module which lists components.
     */
	function list_components() {
		$module = new module('com_configure', 'list', 'content');
		$module->title = "Configure Components";

        $module->components = array_keys($this->config_files);

		if ( empty($module->components) ) {
            $module->detach();
            display_notice("There are no configurable components.");
        }
	}

    /**
     * Convert a configuration array into WDDX format and insert it into an
     * existing Pines WDDX configuration file.
     *
     * @param array $array
     * @param string $config_file The config file to modify.
     * @return bool True on success, false on failure.
     */
    function put_wddx_array($array, $config_file) {
        return $this->put_wddx_data(wddx_serialize_value($array), $config_file);
    }

    /**
     * Write WDDX data into an existing Pines WDDX configuration file.
     *
     * @param string $wddx_data The data to write.
     * @param string $config_file The config file to modify.
     * @return bool True on success, false on failure.
     */
    function put_wddx_data($wddx_data, $config_file) {
		if (!file_exists($config_file)) return false;
        if (!($file_contents = file_get_contents($config_file))) return false;
        $pattern = '/(return\s*[(]?\s*)\S.*([)]?\s*;)/';
        $replacement = '$1"#CODEGOESHERE#"$2';
        /* simplified pattern, but it replaces parenthesis...
        $pattern = '/return(\s|[(]).*;/';
        $replacement = 'return "#CODEGOESHERE#";'; */
        $file_contents = preg_replace($pattern, $replacement, $file_contents, 1);
        $file_contents = str_replace('#CODEGOESHERE#', $wddx_data, $file_contents);
        if (!(file_put_contents($config_file, $file_contents))) return false;
        return true;
        /*
        if (!($handle = fopen($config_file, 'r+'))) return false;
        while (!feof($handle)) {
            $line = fgets($handle);
            $pattern = '/^(\s*return\s*[(]?)\S.*([)]?\s*;)/';
            $replacement = '$1"#CODEGOESHERE#"$2';
            $line = preg_replace($pattern, $replacement, $line, 1);
        }
         */
    }
}

?>