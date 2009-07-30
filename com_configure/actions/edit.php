<?php
/**
 * Edit configuration settings.
 *
 * @package Pines
 * @subpackage com_configure
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_configure/edit') ) {
	$config->user_manager->punt_user("You don't have necessary permission.", $config->template->url('com_configure', 'view', null, false));
	return;
}

if (!array_key_exists($_REQUEST['component'], $config->configurator->config_files)) {
    display_error('Given component either does not exist, or has no configuration file!');
    return;
}

$com_configure_list = new module('com_configure', 'edit', 'content');
$com_configure_list->title = 'Editing Configuration for '.$_REQUEST['component'];
$com_configure_list->req_component = $_REQUEST['component'];
$com_configure_list->config = $config->configurator->get_wddx_array($config->configurator->config_files[$_REQUEST['component']]);
//TODO: reimplement this configuration listing to show variables (using object iteration)
//TODO: design a way to define data types and metadata for configuration.
?>
