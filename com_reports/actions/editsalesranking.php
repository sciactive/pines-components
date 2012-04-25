<?php
/**
 * Provide a form to edit a sales ranking.
 *
 * @package Components\reports
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if (!empty($_REQUEST['id'])) {
	if ( !gatekeeper('com_reports/editsalesranking') )
		punt_user(null, pines_url('com_reports', 'editsalesranking', array('id' => $_REQUEST['id'])));
} else {
	if ( !gatekeeper('com_reports/newsalesranking') )
		punt_user(null, pines_url('com_reports', 'editsalesranking'));
}

$entity = com_reports_sales_ranking::factory((int) $_REQUEST['id']);

if ($entity->final) {
	pines_notice('This sales ranking has been finalized.');
	pines_redirect(pines_url('com_reports', 'salesrankings'));
	return;
}

$entity->print_form();

?>