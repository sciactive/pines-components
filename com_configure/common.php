<?php
defined('D_RUN') or die('Direct access prohibited');

if ( isset($config->ability_manager) ) {
	$config->ability_manager->add('com_configure', 'manage', 'Manage Configuration', 'Let the user change configuration settings.');
	$config->ability_manager->add('com_configure', 'list', 'List Configuration', 'Let the user see the current configuration settings.');
}

class com_configure {
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