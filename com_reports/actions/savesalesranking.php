<?php
/**
 * Save changes to a sales ranking.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( isset($_REQUEST['id']) ) {
	if ( !gatekeeper('com_reports/editsalesranking') )
		punt_user('You don\'t have necessary permission.', pines_url('com_reports', 'salesrankings'));
	$ranking = com_reports_sales_ranking::factory((int) $_REQUEST['id']);
	if (!isset($ranking->guid)) {
		pines_error('Requested Sales Rankings id is not accessible.');
		return;
	}
} else {
	if ( !gatekeeper('com_reports/newsalesranking') )
		punt_user('You don\'t have necessary permission.', pines_url('com_reports', 'salesrankings'));
	$ranking = com_reports_sales_ranking::factory();
}

$ranking->start_date = strtotime('00:00', strtotime($_REQUEST['start']));
$ranking->end_date = strtotime('23:59', strtotime($_REQUEST['end']));
$ranking->goals = array_map('intval', $_REQUEST['goals']);

if ($pines->config->com_reports->global_sales_rankings)
	$ranking->ac->other = 1;

if ($ranking->save()) {
	pines_notice('Saved Sales Ranking ['.$ranking->guid.']');
} else {
	$ranking->print_form();
	pines_error('Error saving Sales Ranking. Do you have permission?');
	return;
}

redirect(pines_url('com_reports', 'salesrankings'));

?>