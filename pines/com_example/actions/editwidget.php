<?php
/**
 * Provide a form to edit a widget.
 *
 * @package Pines
 * @subpackage com_example
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if (isset($_REQUEST['id'])) {
	if ( !gatekeeper('com_example/editwidget') )
		punt_user('You don\'t have necessary permission.', pines_url('com_example', 'editwidget', array('id' => $_REQUEST['id'])));
} else {
	if ( !gatekeeper('com_example/newwidget') )
		punt_user('You don\'t have necessary permission.', pines_url('com_example', 'editwidget'));
}

$entity = com_example_widget::factory((int) $_REQUEST['id']);
$entity->print_form();

?>