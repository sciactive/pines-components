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

$entry->name = $_REQUEST['name'];
$entry->enabled = ($_REQUEST['enabled'] == 'ON');

if (empty($entry->name)) {
	$entry->print_form();
	pines_notice('Please specify a name.');
	return;
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