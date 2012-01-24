<?php
/**
 * Delete a set of threads.
 *
 * @package Pines
 * @subpackage com_notes
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_notes/deletethread') )
	punt_user(null, pines_url('com_notes', 'thread/list'));

$list = explode(',', $_REQUEST['id']);
foreach ($list as $cur_thread) {
	$cur_entity = com_notes_thread::factory((int) $cur_thread);
	if ( !isset($cur_entity->guid) || !$cur_entity->delete() )
		$failed_deletes .= (empty($failed_deletes) ? '' : ', ').$cur_thread;
}
if (empty($failed_deletes)) {
	pines_notice('Selected thread(s) deleted successfully.');
} else {
	pines_error('Could not delete threads with given IDs: '.$failed_deletes);
}

pines_redirect(pines_url('com_notes', 'thread/list'));

?>