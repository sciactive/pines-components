<?php
/**
 * Provide a form to edit a package.
 *
 * @package Pines
 * @subpackage com_packager
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if (isset($_REQUEST['id'])) {
	if ( !gatekeeper('com_packager/editpackage') )
		punt_user(null, pines_url('com_packager', 'editpackage', array('id' => $_REQUEST['id'])));
} else {
	if ( !gatekeeper('com_packager/newpackage') )
		punt_user(null, pines_url('com_packager', 'editpackage'));
}

$entity = com_packager_package::factory((int) $_REQUEST['id']);
$entity->print_form();

?>