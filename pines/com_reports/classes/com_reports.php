<?php
/**
 * com_reports class.
 *
 * @package Pines
 * @subpackage com_reports
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Zak Huber <zakhuber@gmail.com>
 * @copyright Zak Huber
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * com_reports main class.
 *
 * Provides a CRM.
 *
 * @package Pines
 * @subpackage com_reports
 */
class com_reports extends component {
	/**
	 * Whether to integrate with com_sales.
	 * 
	 * @var bool $com_sales
	 */
	var $com_sales;

	/**
	 * Check whether com_sales is installed and we should integrate with it.
	 *
	 * Places the result in $this->com_sales.
	 */
	function __construct() {
		global $pines;
		$this->com_sales = $pines->depend->check('component', 'com_sales');
	}
	
	/**
	 * Creates and attaches a module which reports sales.
	 */
	function report_sales($start = 'now', $end = 'now') {
		global $pines;
		$pines->com_pgrid->load();
		$date_start = strtotime($start);
		$date_end = strtotime("+23 hours +59 minutes", strtotime($end));
		
		$form = new module('com_reports', 'form_sales', 'left');
		$head = new module('com_hrm', 'show_calendar_head', 'head');
		$module = new module('com_reports', 'report_sales', 'content');
		if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
			$module->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_reports/report_sales'];
		
		$module->sales = $pines->entity_manager->get_entities(array('gte' => array('p_cdate' => $date_start), 'lte' => array('p_cdate' => $date_end), 'tags' => array('com_sales', 'sale')));

		$form->date[0] = $date_start;
		$form->date[1] = $date_end;
		$module->date[0] = $date_start;
		$module->date[1] = $date_end;
		
		if ( empty($module->sales) ) {
			display_notice("There are no sales to report.");
		}
	}
}

?>