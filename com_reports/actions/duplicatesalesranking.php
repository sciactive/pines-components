<?php
/**
 * Duplicate a sales ranking.
 *
 * @package Pines
 * @subpackage com_reports
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_reports/newsalesranking') )
	punt_user(null, pines_url('com_reports', 'editsalesranking'));

$entity = com_reports_sales_ranking::factory((int) $_REQUEST['id']);
if (!isset($entity->guid)) {
	pines_error('Requested Sales Rankings id is not accessible.');
	$pines->com_reports->list_sales_rankings();
	return;
}
// Create the Duplicate Sales Ranking.
$dupe = com_reports_sales_ranking::factory();
$dupe->name = $entity->name;
$dupe->start_date = $entity->start_date;
$dupe->end_date = $entity->end_date;
$dupe->calc_nh_goals = $entity->calc_nh_goals;
$dupe->only_below = $entity->only_below;
$dupe->top_location = $entity->top_location;
$dupe->sales_goals = $entity->sales_goals;
$dupe->ac = $entity->ac;

// Save the duplicated event.
if ($dupe->save())
	pines_notice('Duplicated Ranking ['.$dupe->name.']');
else
	pines_error('Error duplicating Sales Ranking. Do you have permission?');

pines_redirect(pines_url('com_reports', 'salesrankings'));

?>