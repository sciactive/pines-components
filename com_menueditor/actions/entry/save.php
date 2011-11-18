<?php
/**
 * Save changes to an entry.
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

if ( isset($_REQUEST['id']) ) {
	if ( !gatekeeper('com_menueditor/editentry') )
		punt_user(null, pines_url('com_menueditor', 'entry/list'));
	$entry = com_menueditor_entry::factory((int) $_REQUEST['id']);
	if (!isset($entry->guid)) {
		pines_error('Requested entry id is not accessible.');
		return;
	}
} else {
	if ( !gatekeeper('com_menueditor/newentry') )
		punt_user(null, pines_url('com_menueditor', 'entry/list'));
	$entry = com_menueditor_entry::factory();
}

// Location
if ($_REQUEST['top_menu'] == '--new--') {
	unset($entry->top_menu, $entry->location);
	$entry->position = $_REQUEST['position'];
} else {
	unset($entry->position);
	$entry->top_menu = $_REQUEST['top_menu'];
	$entry->location = $_REQUEST['location'];
}

// Information
$entry->name = $_REQUEST['name'];
$entry->text = $_REQUEST['text'];
$entry->enabled = ($_REQUEST['enabled'] == 'ON');
$entry->sort = ($_REQUEST['sort'] == 'ON');
$entry->link = $_REQUEST['link'];
if (gatekeeper('com_menueditor/jsentry'))
	$entry->onclick = $_REQUEST['onclick'];

// Conditions
$entry->children = ($_REQUEST['children'] == 'ON');
$conditions = (array) json_decode($_REQUEST['conditions']);
$entry->conditions = array();
foreach ($conditions as $cur_condition) {
	if (!isset($cur_condition->values[0], $cur_condition->values[1]))
		continue;
	$entry->conditions[$cur_condition->values[0]] = $cur_condition->values[1];
}

if (empty($entry->name)) {
	$entry->print_form();
	pines_notice('Please specify a name.');
	return;
}

if (isset($entry->top_menu)) {
	if (empty($entry->top_menu)) {
		$entry->print_form();
		pines_notice('Please specify a menu.');
		return;
	}
	if (empty($entry->location)) {
		$entry->print_form();
		pines_notice('Please specify a location.');
		return;
	}
} else {
	if (empty($entry->position)) {
		$entry->print_form();
		pines_notice('Please specify a position.');
		return;
	}
}

if ($pines->config->com_menueditor->global_entries)
	$entry->ac->other = 1;

if ($entry->save()) {
	pines_notice('Saved entry ['.$entry->name.']');
} else {
	pines_error('Error saving entry. Do you have permission?');
}

pines_redirect(pines_url('com_menueditor', 'entry/list'));

?>