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
	punt_user('You don\'t have necessary permission.', pines_url('com_configure', 'edit', array('component' => $_REQUEST['component'])));

if ($pines->configurator->disable_component($_REQUEST['component'])) {
	if ($_REQUEST['component'] == 'com_configure') {
		pines_notice('com_configure has been disabled. If you need it enabled, you will have to do it manually. To do this, under the components directory, rename ".com_configure" to "com_configure".');
		redirect(pines_url());
	} else {
		pines_notice("Component {$_REQUEST['component']} successfully disabled.");
		redirect(pines_url('com_configure', 'list'));
	}
	exit;
} else {
	pines_error("Couldn't disable component {$_REQUEST['component']}.");
}

redirect(pines_url('com_configure', 'list'));

?>