<?php
/**
 * Get all the attached threads and return a JSON structure.
 *
 * @package Components\notes
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_notes/seethreads') )
	punt_user(null, pines_url('com_notes', 'thread/list'));

$pines->page->override = true;

$entity = $pines->entity_manager->get_entity((int) $_REQUEST['id']);
if (!isset($entity->guid)) {
	$pines->page->override_doc(json_encode(false));
	return;
}

$threads = $pines->entity_manager->get_entities(
		array('class' => com_notes_thread),
		array('&',
			'tag' => array('com_notes', 'thread'),
			'ref' => array('entities', $entity)
		),
		array('!&',
			'strict' => array('hidden', true)
		)
	);
// Order threads by their modification date.
$pines->entity_manager->sort($threads, 'p_mdate');

$return = array();
foreach ($threads as $cur_thread) {
	$cur_struct = array(
		'guid' => $cur_thread->guid,
		'date' => format_date($cur_thread->p_cdate, 'date_short'),
		'user' => $cur_thread->user->name,
		'privacy' => ($cur_thread->ac->other ? 'everyone' : ($cur_thread->ac->group ? 'my-group' : 'only-me')),
		'notes' => array()
	);
	foreach ($cur_thread->notes as $key => $cur_note) {
		$cur_struct['notes'][] = array(
			'key' => $key,
			'date' => format_date($cur_note['date'], 'date_short'),
			'time' => format_date($cur_note['date'], 'time_short'),
			'user' => $cur_note['user']->name,
			'text' => $cur_note['text']
		);
	}
	$return[] = $cur_struct;
}

$pines->page->override_doc(json_encode($return));

?>