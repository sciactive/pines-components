<?php
/**
 * Provide a dialog to edit an entry.
 *
 * @package Pines
 * @subpackage com_menueditor
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper() )
	punt_user(null, pines_url('com_menueditor', 'entry/dialog'));

$entity = com_menueditor_entry::factory();
// Fill in the defaults provided.
if (!empty($_REQUEST['defaults'])) {
	$vars = (array) json_decode($_REQUEST['defaults'], true);
	foreach ($vars as $key => $cur_value) {
		if (in_array($key, array('top_menu', 'position', 'location', 'name', 'text', 'sort_order', 'enabled', 'sort', 'onclick', 'children', 'conditions')))
			$entity->$key = $cur_value;
	}
}
// Fill in the values provided.
if (!empty($_REQUEST['values'])) {
	$vars = (array) json_decode($_REQUEST['values'], true);
	foreach ($vars as $key => $cur_value) {
		if (in_array($key, array('top_menu', 'position', 'location', 'name', 'text', 'sort_order', 'enabled', 'sort', 'onclick', 'children', 'conditions')))
			$entity->$key = $cur_value;
	}
	if (isset($entity->position))
		unset($entity->top_menu);
}

$module = $entity->print_form(true);
$module->dialog = true;

if (!empty($_REQUEST['disabled_fields']))
	$module->disabled_fields = explode(',', $_REQUEST['disabled_fields']);

?>