<?php
/**
 * View configuration settings.
 *
 * @package Pines
 * @subpackage com_configure
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_configure/view') )
	punt_user('You don\'t have necessary permission.', pines_url('com_configure', 'view', $_GET));

if (!array_key_exists($_REQUEST['component'], $pines->configurator->component_files)) {
	pines_error('Given component either does not exist, or has no configuration file!');
	return;
}

$component = configurator_component::factory($_REQUEST['component']);
$component->print_view();

?>