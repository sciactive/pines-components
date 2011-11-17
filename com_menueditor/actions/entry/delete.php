<?php
/**
 * Delete a set of entries.
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

if ( !gatekeeper('com_menueditor/deleteentry') )
	punt_user(null, pines_url('com_menueditor', 'entry/list'));

$list = explode(',', $_REQUEST['id']);
foreach ($list as $cur_entry) {
	$cur_entity = com_menueditor_entry::factory((int) $cur_entry);
	if ( !isset($cur_entity->guid) || !$cur_entity->delete() )
		$failed_deletes .= (empty($failed_deletes) ? '' : ', ').$cur_entry;
}
if (empty($failed_deletes)) {
	pines_notice('Selected entry(s) deleted successfully.');
} else {
	pines_error('Could not delete entries with given IDs: '.$failed_deletes);
}

pines_redirect(pines_url('com_menueditor', 'entry/list'));

?>