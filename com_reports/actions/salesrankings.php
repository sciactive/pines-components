<?php
/**
 * List the monthly sales rankings or show the current one.
 *
 * @package Pines
 * @subpackage com_reports
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_reports/listsalesrankings') ) {
	if ( !gatekeeper('com_reports/viewsalesranking') )
		punt_user('You don\'t have necessary permission.', pines_url('com_reports', 'salesrankings'));
	$current_rankings = $pines->entity_manager->get_entity(array('tags' => array('com_reports', 'sales_ranking'), 'class' => com_reports_sales_ranking));
	if (isset($current_rankings->guid))
		$current_rankings->rank();
} else {
	$pines->com_reports->list_sales_rankings();
}

?>