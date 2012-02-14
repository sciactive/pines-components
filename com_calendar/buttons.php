<?php
/**
 * com_calendar's buttons.
 *
 * @package Pines
 * @subpackage com_calendar
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'calendar' => array(
		'description' => 'Company schedule.',
		'text' => 'Calendar',
		'class' => 'picon-view-calendar',
		'href' => pines_url('com_calendar', 'editcalendar'),
		'default' => true,
		'depends' => array(
			'ability' => 'com_calendar/managecalendar|com_calendar/editcalendar|com_calendar/viewcalendar',
		),
	),
	'calendar_month' => array(
		'description' => 'Company schedule - Month view.',
		'text' => 'This Month',
		'class' => 'picon-view-calendar-month',
		'href' => pines_url('com_calendar', 'editcalendar', array('view_type' => 'month')),
		'default' => false,
		'depends' => array(
			'ability' => 'com_calendar/managecalendar|com_calendar/editcalendar|com_calendar/viewcalendar',
		),
	),
	'calendar_week' => array(
		'description' => 'Company schedule - Week view.',
		'text' => 'This Week',
		'class' => 'picon-view-calendar-week',
		'href' => pines_url('com_calendar', 'editcalendar', array('view_type' => 'agendaWeek')),
		'default' => false,
		'depends' => array(
			'ability' => 'com_calendar/managecalendar|com_calendar/editcalendar|com_calendar/viewcalendar',
		),
	),
	'calendar_day' => array(
		'description' => 'Company schedule - Day view.',
		'text' => 'Today',
		'class' => 'picon-view-calendar-day',
		'href' => pines_url('com_calendar', 'editcalendar', array('view_type' => 'agendaDay')),
		'default' => false,
		'depends' => array(
			'ability' => 'com_calendar/managecalendar|com_calendar/editcalendar|com_calendar/viewcalendar',
		),
	),
);

?>