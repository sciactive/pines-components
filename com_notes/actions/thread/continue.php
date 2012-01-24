<?php
/**
 * Continue a thread.
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

if ( !gatekeeper('com_notes/continueownthread') && !gatekeeper('com_notes/continuethread') )
	punt_user(null, pines_url('com_notes', 'thread/list'));

$pines->page->override = true;

if ($_REQUEST['text'] == '') {
	$pines->page->override_doc(json_encode(false));
	return;
}

// Get the thread.
$thread = com_notes_thread::factory((int) $_REQUEST['id']);
if (!isset($thread->guid)) {
	$pines->page->override_doc(json_encode(false));
	return;
}

// Check their ability.
if (!gatekeeper('com_notes/continuethread') && !$_SESSION['user']->is($thread->user)) {
	// User doesn't have permission to comment on others' threads.
	$pines->page->override_doc(json_encode(false));
	return;
}

// Add the note.
$thread->notes[uniqid()] = array(
	'date' => time(),
	'user' => $_SESSION['user'],
	'text' => $_REQUEST['text']
);

if ($thread->save()) {
	$pines->page->override_doc(json_encode(true));
} else {
	$pines->page->override_doc(json_encode(false));
}

?>