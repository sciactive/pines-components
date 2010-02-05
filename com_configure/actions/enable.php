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
	punt_user('You don\'t have necessary permission.', pines_url('com_configure', 'edit', $_GET, false));

if ($pines->configurator->enable_component($_REQUEST['component'])) {
	$cur_loc = pines_url('com_configure', 'list', array('message' => urlencode('Component '.$_REQUEST['component'].' successfully enabled.')));
	header('Location: '.$cur_loc);
	return;
	// Don't add it to enabled yet, because it didn't have a chance to load itself.
	//$pines->components[] = $_REQUEST['component'];
} else {
	display_error('Couldn\'t enable component '.$_REQUEST['component'].'.');
}

$pines->configurator->list_components();

?>