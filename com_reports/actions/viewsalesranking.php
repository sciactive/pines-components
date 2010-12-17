<?php
/**
 * Provide a view for a sales ranking.
 *
 * @package Pines
 * @subpackage com_reports
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_reports/viewsalesranking') )
	punt_user(null, pines_url('com_reports', 'viewsalesranking', array('id' => $_REQUEST['id'])));

$entity = com_reports_sales_ranking::factory((int) $_REQUEST['id']);
if (!isset($entity->guid)) {
	pines_error('Requested Sales Rankings id is not accessible.');
	$pines->com_reports->list_sales_rankings();
	return;
}

$location = group::factory((int) $_REQUEST['location']);
$descendents = ($_REQUEST['descendents'] == 'true');

$entity->rank($location, $descendents);

?>