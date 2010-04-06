<?php
/**
 * Provide a form to edit an user template.
 *
 * @package Pines
 * @subpackage com_hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if (isset($_REQUEST['id'])) {
	if ( !gatekeeper('com_hrm/editusertemplate') )
		punt_user('You don\'t have necessary permission.', pines_url('com_hrm', 'editusertemplate', array('id' => $_REQUEST['id'])));
} else {
	if ( !gatekeeper('com_hrm/newusertemplate') )
		punt_user('You don\'t have necessary permission.', pines_url('com_hrm', 'editusertemplate'));
}

$entity = com_hrm_user_template::factory((int) $_REQUEST['id']);
$entity->print_form();

?>