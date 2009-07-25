<?php
/**
 * List configuration settings and locations.
 *
 * @package Pines
 * @subpackage com_configure
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_configure/list') ) {
	$config->user_manager->punt_user("You don't have necessary permission.", $config->template->url('com_configure', 'list', null, false));
	return;
}

$com_configure_list = new module('com_configure', 'list', 'content');
$com_configure_list->title = 'Configuration';
//TODO: reimplement this configuration listing to show variables (using object iteration)
//TODO: design a way to define data types and metadata for configuration.
?>
