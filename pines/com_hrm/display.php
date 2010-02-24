<?php
/**
 * com_hrm's display control.
 *
 * @package Pines
 * @subpackage com_hrm
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if (gatekeeper('com_hrm/editcalendar') || gatekeeper('com_hrm/viewcalendar') ||
	gatekeeper('com_hrm/manageclock') ||
	gatekeeper('com_hrm/listemployees') || gatekeeper('com_hrm/newemployee') ||
	gatekeeper('com_hrm/listusertemplates') || gatekeeper('com_hrm/newusertemplate') ) {
	$com_hrm_menu_id = $pines->page->main_menu->add('HRM');
	if ( gatekeeper('com_hrm/editcalendar') || gatekeeper('com_hrm/viewcalendar') )
		$pines->page->main_menu->add('Calendar', pines_url('com_hrm', 'editcalendar'), $com_hrm_menu_id);
	if ( gatekeeper('com_hrm/manageclock') )
		$pines->page->main_menu->add('Timeclock', pines_url('com_hrm', 'listtimeclocks'), $com_hrm_menu_id);
	if ( gatekeeper('com_hrm/listemployees') )
		$pines->page->main_menu->add('Employees', pines_url('com_hrm', 'listemployees'), $com_hrm_menu_id);
	if ( gatekeeper('com_hrm/newemployee') )
		$pines->page->main_menu->add('New Employee', pines_url('com_hrm', 'editemployee'), $com_hrm_menu_id);
	if ( gatekeeper('com_hrm/listusertemplates') )
		$pines->page->main_menu->add('User Templates', pines_url('com_hrm', 'listusertemplates'), $com_hrm_menu_id);
	if ( gatekeeper('com_hrm/newusertemplate') )
		$pines->page->main_menu->add('New User Template', pines_url('com_hrm', 'editusertemplate'), $com_hrm_menu_id);
}

$pines->com_hrm->provide_clockin();

?>