<?php
/**
 * Disable a component.
 *
 * @package Pines
 * @subpackage com_configure
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_configure/edit') )
	$config->user_manager->punt_user("You don't have necessary permission.", pines_url('com_configure', 'edit', $_GET, false));

if ($config->configurator->disable_component($_REQUEST['component'])) {
	$cur_loc = pines_url('com_configure', 'list', array('message' => urlencode('Component '.$_REQUEST['component'].' successfully disabled.')));
	header('Location: '.$cur_loc);
	return;
} else {
	display_error("Couldn't disable component {$_REQUEST['component']}.");
}

$config->configurator->list_components();

?>