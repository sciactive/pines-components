<?php
/**
 * Update loan entities.
 *
 * @package Components\loan
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <angela@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('system/all') )
    punt_user('You don\'t have necessary permission.', pines_url());

$module = new module('system', 'null', 'content');
$module->title = 'Loan Entity Update';

$errors = array();
$count = $nochange = 0;
// Grab entities and update.
$entities = $pines->entity_manager->get_entities(
		array('class' => com_loan_loan),
		array('&',
			'tag' => array('com_loan', 'loan'),
		)
	);

foreach ($entities as &$cur_entity) {
	$changed = false;
	if (format_date($cur_entity->first_payment_date, 'custom', 'H:i') != '00:00') {
		$cur_entity->first_payment_date = strtotime('00:00:00', $cur_entity->first_payment_date);
		$changed = true;
	}
	foreach ($cur_entity->schedule as &$schedule) {
		if (format_date($schedule['scheduled_date_expected'], 'custom', 'H:i') != '00:00') {
			$schedule['scheduled_date_expected'] = strtotime('00:00:00', $schedule['scheduled_date_expected']);
			$changed = true;
		}
	}
	unset($schedule);
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

$module->content("Updated $count entities. Found $nochange entities that didn't need to be updated.");
if ($errors)
	$module->content('<br />Could not update the entities: '.implode(', ', $errors));

?>