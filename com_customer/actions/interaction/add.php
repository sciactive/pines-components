<?php
/**
 * Log a customer interaction.
 *
 * @package Pines
 * @subpackage com_customer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_customer/newinteraction') )
	punt_user(null, pines_url('com_customer', 'interaction/add'));

$pines->page->override = true;

$customer = com_customer_customer::factory(intval($_REQUEST['customer']));
if (!isset($customer->guid))
	$customer = com_customer_company::factory(intval($_REQUEST['customer']));

if (!isset($customer->guid)) {
	$pines->page->override_doc('false');
	return;
}

$employee = com_hrm_employee::factory((int) $_REQUEST['employee']);
if (!isset($employee)) {
	$pines->page->override_doc('false');
	return;
}

$interaction = com_customer_interaction::factory();
$interaction->customer = $customer;
$interaction->employee = $employee;
// Change the timezone to enter the event with the user's timezone.
date_default_timezone_set($_SESSION['user']->get_timezone());
$date = strtotime($_REQUEST['date']);
$date_month = date('n', $date);
$date_day = date('j', $date);
$date_year = date('Y', $date);
$time_hour = ($_REQUEST['time_ampm'] == 'am') ? $_REQUEST['time_hour'] : $_REQUEST['time_hour'] + 12;
$interaction->action_date = mktime($time_hour,$_REQUEST['time_minute'],0,$date_month,$date_day,$date_year);
$interaction->type = $_REQUEST['type'];
$interaction->status = $_REQUEST['status'];
$interaction->comments = $_REQUEST['comments'];

$existing_appt = $pines->entity_manager->get_entity(
	array('class' => com_customer_interaction),
	array('&',
		'data' => array('status', 'open'),
		'ref' => array('customer', $interaction->customer),
		'gte' => array('action_date', $interaction->action_date),
		'lte' => array('action_date', strtotime('+1 hour', $interaction->action_date))
	)
);
if (isset($existing_appt->guid) && $interaction->guid != $existing_appt->guid) {
	$pines->page->override_doc('"conflict"');
	return;
}

if ($pines->config->com_customer->com_calendar) {
	// Create the interaction calendar event.
	$event = com_calendar_event::factory();
	$event->employee = $employee;
	$location = $employee->group;
	$event->appointment = true;
	$event->label = $interaction->type;
	foreach ($pines->config->com_customer->interaction_types as $cur_type) {
		if (strpos($cur_type, $interaction->type))
			$symbol = explode(':', $cur_type);
	}
	$event->title = $symbol[0] .' '. $customer->name;
	$event->private = true;
	$event->all_day = false;
	$event->start = $interaction->action_date;
	$event->end = strtotime('+1 hour', $interaction->action_date);
	switch ($interaction->status) {
		case 'open':
		default:
			$event->color = 'greenyellow';
			break;
		case 'canceled':
			$event->color = 'gainsboro';
			break;
		case 'closed':
			$event->color = 'blue';
			break;
	}
	$event->information = '('.$interaction->employee->name.') '.$interaction->comments;
	$event->ac->other = 2;
	if (!$event->save()) {
		$pines->page->override_doc('false');
		return;
	}

	$interaction->event = $event;
}

$interaction->ac->other = 2;

if ($interaction->save()) {
	if ($pines->config->com_customer->com_calendar) {
		$event->appointment = $interaction;
		$event->group = $location;
		$event->save();
	}
	$pines->page->override_doc('true');
} else {
	$pines->page->override_doc('false');
}

?>