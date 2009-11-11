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
	$config->user_manager->punt_user("You don't have necessary permission.", pines_url('com_configure', 'edit', $_GET, false));
	return;
}

if (!array_key_exists($_REQUEST['component'], $config->configurator->config_files)) {
    display_error('Given component either does not exist, or has no configuration file!');
    return;
}

$list = new module('com_configure', 'edit', 'content');
$list->req_component = htmlentities($_REQUEST['component']);
$list->config = $config->configurator->get_config_array($config->configurator->config_files[$_REQUEST['component']]);

?>