<?php
/**
 * Save changes to a sales ranking.
 *
 * @package Components\reports
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( isset($_REQUEST['id']) ) {
	if ( !gatekeeper('com_reports/editsalesranking') )
		punt_user(null, pines_url('com_reports', 'salesrankings'));
	$ranking = com_reports_sales_ranking::factory((int) $_REQUEST['id']);
	if (!isset($ranking->guid)) {
		pines_error('Requested Sales Rankings id is not accessible.');
		return;
	}
	if ($ranking->final) {
		pines_notice('This sales ranking has been finalized.');
		pines_redirect(pines_url('com_reports', 'salesrankings'));
		return;
	}
} else {
	if ( !gatekeeper('com_reports/newsalesranking') )
		punt_user(null, pines_url('com_reports', 'salesrankings'));
	$ranking = com_reports_sales_ranking::factory();
}

$ranking->name = $_REQUEST['ranking_name'];
$ranking->start_date = strtotime('00:00:00', strtotime($_REQUEST['start']));
$ranking->end_date = strtotime('23:59:59', strtotime($_REQUEST['end'])) + 1;
$ranking->exclude_pending_contracts = ($_REQUEST['exclude_pending_contracts'] == 'ON');
$ranking->calc_nh_goals = ($_REQUEST['calc_nh_goals'] == 'ON');
$ranking->only_below = ($_REQUEST['only_below'] == 'ON');
$ranking->top_location = group::factory((int) $_REQUEST['top_location']);
if (!isset($ranking->top_location->guid))
	$ranking->top_location = $_SESSION['user']->group;
$sales_goals = (array) json_decode($_REQUEST['sales_goals'], true);
$ranking->sales_goals = array();
foreach ($sales_goals as $key => $cur_goal_rank) {
	$ranking->sales_goals[(int) $key] = array(
		'goal' => (float) $cur_goal_rank['goal'],
		'rank' => ($cur_goal_rank['rank'] == '' ? null : (int) $cur_goal_rank['rank'])
	);
}

if ($pines->config->com_reports->global_sales_rankings)
	$ranking->ac->other = 1;

if ($ranking->save()) {
	pines_notice('Saved Sales Ranking ['.$ranking->name.']');
} else {
	$ranking->print_form();
	pines_error('Error saving Sales Ranking. Do you have permission?');
	return;
}

pines_redirect(pines_url('com_reports', 'salesrankings'));

?>