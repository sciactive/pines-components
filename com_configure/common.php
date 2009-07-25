<?php
/**
 * com_configure's common file.
 *
 * @package Pines
 * @subpackage com_configure
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( isset($config->ability_manager) ) {
	$config->ability_manager->add('com_configure', 'manage', 'Manage Configuration', 'Let the user change configuration settings.');
	$config->ability_manager->add('com_configure', 'list', 'List Configuration', 'Let the user see the current configuration settings.');
}

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

	function configure() {
		return;
	}
}

$config->configurator = new com_configure;

?>