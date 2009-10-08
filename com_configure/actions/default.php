<?php
/**
 * Edit the system configuration.
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
	$config->user_manager->punt_user("You don't have necessary permission.", $config->template->url('com_configure', 'default', false));
	return;
}

$list = new module('com_configure', 'edit', 'content');
$list->title = 'Editing Configuration for system';
$list->req_component = 'system';
$list->config = $config->configurator->get_config_array($config->configurator->config_files['system']);

?>