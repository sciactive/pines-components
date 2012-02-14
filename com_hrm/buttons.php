<?php
/**
 * com_hrm's buttons.
 *
 * @package Pines
 * @subpackage com_hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'timeclock' => array(
		'description' => 'Timeclock manager.',
		'text' => 'Timeclock',
		'class' => 'picon-view-calendar-time-spent',
		'href' => pines_url('com_hrm', 'employee/timeclock/list'),
		'default' => false,
		'depends' => array(
			'ability' => 'com_hrm/manageclock',
		),
	),
	'employees' => array(
		'description' => 'Employee manager.',
		'text' => 'Employees',
		'class' => 'picon-text-directory',
		'href' => pines_url('com_hrm', 'employee/list'),
		'default' => false,
		'depends' => array(
			'ability' => 'com_hrm/listemployees',
		),
	),
	'work_history' => array(
		'description' => 'Work history.',
		'text' => 'Work History',
		'class' => 'picon-view-history',
		'href' => pines_url('com_hrm', 'employee/history'),
		'default' => false,
		'depends' => array(
			'ability' => 'com_hrm/viewownhistory',
		),
	),
	'bonuses' => array(
		'description' => 'Bonuses.',
		'text' => 'Bonuses',
		'class' => 'picon-games-achievements',
		'href' => pines_url('com_hrm', 'bonus/list'),
		'default' => false,
		'depends' => array(
			'ability' => 'com_hrm/listbonuses',
		),
	),
	'adjustments' => array(
		'description' => 'Adjustments.',
		'text' => 'Adjustments',
		'class' => 'picon-view-financial-transfer',
		'href' => pines_url('com_hrm', 'adjustment/list'),
		'default' => false,
		'depends' => array(
			'ability' => 'com_hrm/listadjustments',
		),
	),
	'applications' => array(
		'description' => 'Applications.',
		'text' => 'Applications',
		'class' => 'picon-story-editor',
		'href' => pines_url('com_hrm', 'application/list'),
		'default' => false,
		'depends' => array(
			'ability' => 'com_hrm/listapplications',
		),
	),
);

?>