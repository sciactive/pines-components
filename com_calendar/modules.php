<?php
/**
 * com_calendar's modules.
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

global $pines;
return array(
	'agenda' => array(
		'cname' => 'Agenda',
		'description' => 'Show your agenda.',
		'image' => 'includes/agenda_screen.png',
		'view_callback' => array($pines->com_calendar, 'agenda_widget'),
		'form' => 'modules/agenda_form',
		'type' => 'module imodule widget',
		'widget' => array(
			'default' => true,
			'depends' => array(
				'ability' => 'com_calendar/viewcalendar',
			),
		),
	),
);

?>