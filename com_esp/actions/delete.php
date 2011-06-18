<?php
/**
 * Delete Extended Service Plans.
 *
 * @package Pines
 * @subpackage com_esp
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_esp/deleteplan') )
	punt_user(null, pines_url('com_esp', 'delete'));

$list = explode(',', $_REQUEST['id']);
foreach ($list as $cur_plan) {
	$cur_entity = com_esp_plan::factory((int) $cur_plan);
	if ( !isset($cur_entity->guid) || !$cur_entity->delete() )
		$failed_deletes .= (empty($failed_deletes) ? '' : ', ').$cur_plan;
}
if (empty($failed_deletes)) {
	pines_notice('Selected ESP(s) deleted successfully.');
} else {
	pines_error('Could not delete ESP(s) with given ID(s): '.$failed_deletes);
}

pines_redirect(pines_url('com_esp', 'list'));

?>