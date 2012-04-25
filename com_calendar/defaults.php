<?php
/**
 * com_calendar's configuration defaults.
 *
 * @package Components\calendar
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	array(
		'name' => 'com_customer',
		'cname' => 'CRM Integration',
		'description' => 'Integrate with com_customer.',
		'value' => true,
		'peruser' => true,
	),
	array(
		'name' => 'edit_appointments',
		'cname' => 'Edit Appointments',
		'description' => 'Appointment events can be edited.',
		'value' => true,
		'peruser' => true,
	),
	array(
		'name' => 'workday_length',
		'cname' => 'Default Workday Length',
		'description' => 'The amount of work hours in a full workday.',
		'value' => 8,
		'peruser' => true,
	),
	array(
		'name' => 'lineup_shifts',
		'cname' => 'Quick Schedule Shifts',
		'description' => 'The shift times for creating company schedule lineups. (24 time format)',
		'value' => array(
			'8:00-16:00',
			'9:00-17:00',
			'10:00-14:00',
			'14:00-17:00',
			'17:00-21:00'
		),
		'peruser' => true,
	),
);

?>