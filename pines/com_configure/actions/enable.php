<?php
/**
 * Disable a component.
 *
 * @package Pines
 * @subpackage com_configure
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_configure/edit') )
	punt_user('You don\'t have necessary permission.', pines_url('com_configure', 'edit', $_GET));

if ($pines->configurator->enable_component($_REQUEST['component'])) {
	pines_notice("Component {$_REQUEST['component']} successfully enabled.");
	redirect(pines_url('com_configure', 'list', array('message' => urlencode())));
	exit;
} else {
	pines_error('Couldn\'t enable component '.$_REQUEST['component'].'.');
}

$pines->configurator->list_components();

?>