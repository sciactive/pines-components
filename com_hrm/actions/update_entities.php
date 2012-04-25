<?php
/**
 * Update entities.
 *
 * @package Components
 * @subpackage hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('system/all') )
    punt_user('You don\'t have necessary permission.', pines_url());

$module = new module('system', 'null', 'content');
$module->title = 'Entity Update';

$errors = array();
$offset = $count = $nochange = 0;
// Grab entities, 50 at a time, and update.
do {
	$entities = $pines->entity_manager->get_entities(
			array('limit' => 50, 'offset' => $offset, 'class' => com_hrm_timeclock),
			array('&',
				'tag' => array('com_hrm', 'timeclock')
			)
		);

	foreach ($entities as &$cur_entity) {
		$changed = false;
		if ((array) $cur_entity->timeclock === $cur_entity->timeclock) {
			$changed = true;
			foreach ($cur_entity->timeclock as $key => $cur_entry) {
				// Check that it doesn't already exist.
				$prev_entry = $pines->entity_manager->get_entity(
						array('class' => com_hrm_timeclock_entry),
						array('&',
							'tag' => array('com_hrm', 'timeclock_entry'),
							'data' => array(
								array('in', (int) $cur_entry['in']),
								array('out', (int) $cur_entry['out'])
							)
						)
					);
				if (!isset($prev_entry)) {
					// If it doesn't, make a new timeclock entry.
					$new_entry = com_hrm_timeclock_entry::factory();
					$new_entry->in = (int) $cur_entry['in'];
					$new_entry->out = (int) $cur_entry['out'];
					$new_entry->comment = (string) $cur_entry['comment'];
					$new_entry->extras = (array) $cur_entry['extras'];
					if ($new_entry->save()) {
						$new_entry->user = $cur_entity->user;
						$new_entry->group = $cur_entity->group;
						$new_entry->save();
					}
				}
				// Now remove this entry from the timeclock array.
				unset($cur_entity->timeclock[$key]);
			}
			// Now unset the timeclock array. We're all done.
			unset($cur_entity->timeclock);
		}
		if ($changed) {
			if ($cur_entity->save())
				$count++;
			else
				$errors[] = $cur_entity->guid;
		} else {
			$nochange++;
		}
	}
	unset($cur_entity);
	$offset += 50;
} while (!empty($entities));

$module->content("Updated $count entities. Found $nochange entities that didn't need to be updated.");
if ($errors)
	$module->content('<br />Could not update the entities: '.implode(', ', $errors));

?>