<?php
/**
 * Delete sales ranking(s).
 *
 * @package Pines
 * @subpackage com_reports
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_reports/deletesalesranking') )
	punt_user(null, pines_url('com_reports', 'deletesalesranking', array('id' => $_REQUEST['id'])));

$list = explode(',', $_REQUEST['id']);
foreach ($list as $cur_ranking) {
	$cur_entity = com_reports_sales_ranking::factory((int) $cur_ranking);
	if ( !isset($cur_entity->guid) || !$cur_entity->delete() )
		$failed_deletes .= (empty($failed_deletes) ? '' : ', ').$cur_ranking;
}
if (empty($failed_deletes)) {
	pines_notice('Selected sales ranking(s) deleted successfully.');
} else {
	pines_error('Could not delete sales rankings with given IDs: '.$failed_deletes);
}

redirect(pines_url('com_reports', 'salesrankings'));

?>