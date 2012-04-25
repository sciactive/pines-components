<?php
/**
 * Delete a dashboard.
 *
 * @package Components\dash
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_dash/manage') )
	punt_user(null, pines_url('com_dash', 'manage/list'));

$list = explode(',', $_REQUEST['id']);
foreach ($list as $cur_dashboard) {
	$cur_entity = com_dash_dashboard::factory((int) $cur_dashboard);
	if ( !isset($cur_entity->guid) || !$cur_entity->delete() )
		$failed_deletes .= (empty($failed_deletes) ? '' : ', ').$cur_dashboard;
}
if (empty($failed_deletes)) {
	pines_notice('Selected dashboard(s) deleted successfully.');
} else {
	pines_error('Could not delete dashboards with given IDs: '.$failed_deletes);
}

pines_redirect(pines_url('com_dash', 'manage/list'));

?>