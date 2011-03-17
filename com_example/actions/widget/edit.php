<?php
/**
 * Provide a form to edit a widget.
 *
 * @package Pines
 * @subpackage com_example
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if (!empty($_REQUEST['id'])) {
	if ( !gatekeeper('com_example/editwidget') )
		punt_user(null, pines_url('com_example', 'widget/edit', array('id' => $_REQUEST['id'])));
} else {
	if ( !gatekeeper('com_example/newwidget') )
		punt_user(null, pines_url('com_example', 'widget/edit'));
}

$entity = com_example_widget::factory((int) $_REQUEST['id']);
$entity->print_form();

?>