<?php
/**
 * Add menu entries.
 *
 * @package Pines
 * @subpackage com_reports
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Zak Huber <zakhuber@gmail.com>
 * @copyright Zak Huber
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if (gatekeeper('com_reports/listsales') ||
	gatekeeper('com_reports/listinventory') ||
	gatekeeper('com_reports/listemployees')) {

	$com_reports_menu_id = $pines->page->main_menu->add('Reports');
	if ( gatekeeper('com_reports/listsales') )
		$pines->page->main_menu->add('Sales Report', pines_url('com_reports', 'reportsales'), $com_reports_menu_id);
	if ( gatekeeper('com_reports/listinventory') )
		$pines->page->main_menu->add('Inventory Report', pines_url('com_reports', 'reportinventory'), $com_reports_menu_id);
	if (gatekeeper('com_reports/listemployees') )
		$pines->page->main_menu->add('Employee Report', pines_url('com_reports', 'reportemployees'), $com_reports_menu_id);
}

?>