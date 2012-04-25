<?php
/**
 * Provide a form to edit an entry.
 *
 * @package Components\menueditor
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if (!empty($_REQUEST['id'])) {
	if ( !gatekeeper('com_menueditor/editentry') )
		punt_user(null, pines_url('com_menueditor', 'entry/edit', array('id' => $_REQUEST['id'])));
} else {
	if ( !gatekeeper('com_menueditor/newentry') )
		punt_user(null, pines_url('com_menueditor', 'entry/edit'));
}

$entity = com_menueditor_entry::factory((int) $_REQUEST['id']);
$entity->print_form();

?>