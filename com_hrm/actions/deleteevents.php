<?php
/**
 * Remove a calendar event.
 *
 * @package Pines
 * @subpackage com_hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_hrm/editcalendar') )
	punt_user('You don\'t have necessary permission.', pines_url('com_hrm', 'editcalendar'));

$pines->page->override = true;

$list = array_map('intval', (array) $_REQUEST['events']);
$failed_removes = array();
foreach ($list as $cur_id) {
	$cur_entity = com_hrm_event::factory((int) $cur_id);
	$group = $cur_entity->event_id;
	if ( !isset($cur_entity->guid) || !$cur_entity->delete() ) {
		$failed_removes[] = $cur_id;
	} elseif ($group != 0) {
		$events = $pines->entity_manager->get_entities(
				array('class' => com_hrm_event),
				array('!&',
					'guid' => $list
				),
				array('&',
					'data' => array('event_id', $group),
					'tag' => array('com_hrm', 'event')
				)
			);
		foreach ($events as $cur_event) {
			if ( !isset($cur_event->guid) || !$cur_event->delete() )
				$failed_removes[] = $cur_id;
		}
	}
}
if (!empty($failed_removes))
	$pines->page->override_doc(json_encode($failed_removes));

?>