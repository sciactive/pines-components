<?php
/**
 * Provide a view for a sales ranking.
 *
 * @package Components\reports
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_reports/viewsalesranking') )
	punt_user(null, pines_url('com_reports', 'viewsalesranking', array('id' => $_REQUEST['id'])));

if ($_REQUEST['id'] === 'latest') {
	$entity = $pines->entity_manager->get_entity(
			array('class' => com_reports_sales_ranking, 'reverse' => true),
			array('&',
				'tag' => array('com_reports', 'sales_ranking')
			)
		);
} else {
	$entity = com_reports_sales_ranking::factory((int) $_REQUEST['id']);
}
if (!isset($entity->guid)) {
	pines_error('Requested Sales Rankings id is not accessible.');
	pines_redirect(pines_url('com_reports', 'salesrankings'));
	return;
}

$entity->rank();

?>