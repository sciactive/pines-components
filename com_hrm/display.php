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

if ( gatekeeper('com_hrm/listemployees') || gatekeeper('com_hrm/newemployee') ) {
	$com_hrm_menu_id = $config->page->main_menu->add('HRM');
	if ( gatekeeper('com_hrm/listemployees') )
		$config->page->main_menu->add('Employees', pines_url('com_hrm', 'listemployees'), $com_hrm_menu_id);
//	if ( gatekeeper('com_sales/manageclock') )
//		$config->page->main_menu->add('Timeclock', pines_url('com_sales', 'manageclock'), $com_sales_menu_id_employees);
	if ( gatekeeper('com_hrm/newemployee') )
		$config->page->main_menu->add('New Employee', pines_url('com_hrm', 'editemployee'), $com_hrm_menu_id);
}

$config->run_hrm->provide_clockin();

?>