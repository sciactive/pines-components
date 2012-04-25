<?php
/**
 * Delete employee bonuses.
 *
 * @package Components\hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_hrm/deletebonus') )
	punt_user(null, pines_url('com_hrm', 'issue/list'));

$list = explode(',', $_REQUEST['id']);
foreach ($list as $cur_bonus) {
	$cur_entity = com_hrm_bonus::factory((int) $cur_bonus);
	if ( !isset($cur_entity->guid) || !$cur_entity->delete() )
		$failed_deletes .= (empty($failed_deletes) ? '' : ', ').$cur_issue;
}
if (empty($failed_deletes)) {
	pines_notice('Selected bonuses deleted successfully.');
} else {
	pines_error('Could not delete bonuses with given IDs: '.$failed_deletes);
}

pines_redirect(pines_url('com_hrm', 'bonus/list'));

?>