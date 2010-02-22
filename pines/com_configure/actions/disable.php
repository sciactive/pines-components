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
	punt_user('You don\'t have necessary permission.', pines_url('com_configure', 'edit', array('component' => $_REQUEST['component']), false));

if ($pines->configurator->disable_component($_REQUEST['component'])) {
	if ($_REQUEST['component'] == 'com_configure') {
		display_notice('com_configure has been disabled. If you need it enabled, you will have to do it manually. To do this, under the components directory, rename ".com_configure" to "com_configure".');
		action($pines->config->default_component, 'default');
	} else {
		$cur_loc = pines_url('com_configure', 'list', array('message' => urlencode('Component '.$_REQUEST['component'].' successfully disabled.')));
		header('Location: '.$cur_loc);
		$pines->page->override = true;
	}
	return;
} else {
	display_error("Couldn't disable component {$_REQUEST['component']}.");
}

$pines->configurator->list_components();

?>