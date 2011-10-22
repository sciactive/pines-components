<?php
/**
 * Finalize a sales ranking.
 *
 * @package Pines
 * @subpackage com_reports
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_reports/editsalesranking') )
	punt_user(null, pines_url('com_reports', 'salesrankings'));
$ranking = com_reports_sales_ranking::factory((int) $_REQUEST['id']);
if (!isset($ranking->guid)) {
	pines_error('Requested Sales Rankings id is not accessible.');
	return;
}
if ($ranking->final) {
	pines_notice('This sales ranking has already been finalized.');
	pines_redirect(pines_url('com_reports', 'salesrankings'));
	return;
}

// This function updates the rankings to current.
$module = $ranking->rank();
// Now detach the ranking module, it is unnecessary.
$module->detach();
$ranking->final = true;
$ranking->final_date = time();

if ($ranking->save())
	pines_notice('Finalized Sales Ranking ['.$ranking->name.']');
else
	pines_error('Error saving Sales Ranking. Do you have permission?');

pines_redirect(pines_url('com_reports', 'salesrankings'));

?>