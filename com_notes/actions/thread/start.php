<?php
/**
 * Save a new thread.
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

if ( !gatekeeper('com_notes/newthread') )
	punt_user(null, pines_url('com_notes', 'thread/list'));

$pines->page->override = true;

$thread = com_notes_thread::factory();

// Using an array leaves the possibility to associate a thread with multiple
// entities in the future.
$thread->entities = array($pines->entity_manager->get_entity((int) $_REQUEST['id']));
$note_id = uniqid();
$thread->notes[$note_id] = array(
	'date' => time(),
	'user' => $_SESSION['user'],
	'text' => $_REQUEST['text']
);
switch ($_REQUEST['privacy']) {
	case 'only-me':
		$thread->ac = (object) array('user' => 3, 'group' => 0, 'other' => 0);
		break;
	case 'my-group':
		$thread->ac = (object) array('user' => 3, 'group' => 2, 'other' => 0);
		break;
	case 'everyone':
	default:
		$thread->ac = (object) array('user' => 3, 'group' => 2, 'other' => 2);
		break;
}

if (!isset($thread->entities[0]->guid) || $thread->notes[$note_id]['text'] == '') {
	$pines->page->override_doc(json_encode(false));
	return;
}

if ($thread->save()) {
	$pines->page->override_doc(json_encode(true));
} else {
	$pines->page->override_doc(json_encode(false));
}

?>