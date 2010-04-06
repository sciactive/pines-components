<?php
/**
 * Disable a component.
 *
 * @package Pines
 * @subpackage com_configure
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_configure/edit') )
	punt_user('You don\'t have necessary permission.', pines_url('com_configure', 'edit', $_GET));

if ($pines->configurator->enable_component($_REQUEST['component'])) {
	header('HTTP/1.1 303 See Other', true, 303);
	header('Location: '.pines_url('com_configure', 'list', array('message' => urlencode("Component {$_REQUEST['component']} successfully enabled."))));
	$pines->page->override = true;
	return;
} else {
	pines_error('Couldn\'t enable component '.$_REQUEST['component'].'.');
}

$pines->configurator->list_components();

?>