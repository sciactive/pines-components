<?php
/**
 * Save changes to an employees timeclock.
 *
 * @package Components\hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_hrm/manageclock') )
	punt_user(null, pines_url('com_hrm', 'employee/timeclock/edit', array('id' => $_REQUEST['id'])));

$employee = com_hrm_employee::factory((int) $_REQUEST['id']);
$employee_user = user::factory((int) $_REQUEST['id']);
if (!isset($employee->guid) || !isset($employee_user->guid)) {
	pines_error('Requested employee id is not accessible.');
	return;
}

// Get the time range of the edit request.
$time_start = (int) $_REQUEST['time_start'];
$time_end = (int) $_REQUEST['time_end'];
if (empty($time_start) || empty($time_end) || $time_end <= $time_start) {
	pines_notice('Invalid times provided. Cowardly refusing to proceed.');
	return;
}

// And gather the current entries in that time range.
$unsorted_entries = $pines->entity_manager->get_entities(
		array('class' => com_hrm_timeclock_entry),
		array('&',
			'tag' => array('com_hrm', 'timeclock_entry'),
			'ref' => array('user', $employee_user),
			'lt' => array('in', $time_end),
			'gt' => array('out', $time_start)
		)
	);
// Now sort them with their GUID as their key.
$entries = array();
foreach ($unsorted_entries as $key => $cur_entry) {
	$entries[$cur_entry->guid] = $cur_entry;
	unset($unsorted_entries[$key]);
}
unset($unsorted_entries);

// Now we probably have the same set given to the user to edit.
// TODO: Figure out how to prevent this from deleting the new entry if the employee clocks in while the user is editing.
$clock = (array) json_decode($_REQUEST['clock']);
foreach ($clock as $cur_entry) {
	$guid = $cur_entry->guid;
	if ($guid > 0) {
		if (isset($entries[$guid])) {
			$entity = $entries[$guid];
			unset($entries[$guid]);
		} else
			// The guid was provided, but not found, so assume it's already been deleted/altered.
			continue;
	} else
		$entity = com_hrm_timeclock_entry::factory();
	$entity->in = (int) $cur_entry->in;
	$entity->out = (int) $cur_entry->out;
	// Make sure the times are valid, else don't save.
	if ($entity->in >= $entity->out) {
		$invalid_times = true;
		continue;
	}
	$entity->comments = (string) $cur_entry->comments;
	$entity->extras = (array) json_decode($cur_entry->extras, true);
	if ($entity->save()) {
		if (!$employee_user->is($entity->user) || (isset($employee_user->group) && !$employee_user->group->is($entity->group))) {
			$entity->user = $employee_user;
			$entity->group = $employee_user->group;
			$entity->save();
		}
	} else {
		$save_error = true;
	}
}
if ($invalid_times)
	pines_notice("At least one invalid time frame was provided. This usually means an entry with a 0 or negative time range. Invalid entries were not saved.");

// Now that we've gone through all the altered entities and removed them from
// the array, anything left over in the array would have been deleted by the
// user.
foreach ($entries as $cur_entry) {
	if (!$cur_entry->delete())
		$delete_error = true;
}

if (!$save_error && !$delete_error) {
	pines_notice("Saved timeclock for {$employee->name}.");
} else {
	if ($save_error)
		pines_error('An error occured saving timeclock entries. Not all entries were saved correctly.');
	if ($delete_error)
		pines_error('An error occured removing timeclock entries. Not all entries marked for deleting were deleted successfully.');
}

pines_redirect(pines_url('com_hrm', 'employee/timeclock/list'));

?>