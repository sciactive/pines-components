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
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_reports/listsalesrankings') ) {
	if ( !gatekeeper('com_reports/viewsalesranking') )
		punt_user(null, pines_url('com_reports', 'salesrankings'));
	$current_rankings = $pines->entity_manager->get_entities(array('class' => com_reports_sales_ranking), array('&', 'tag' => array('com_reports', 'sales_ranking')));
	$current_rankings = end($current_rankings);
	if (isset($current_rankings->guid)) {
		$current_rankings->rank();
	} else {
		pines_notice('No rankings are accessible.');
	}
} else {
	$pines->com_reports->list_sales_rankings();
}

?>