<?php
/**
 * Delete a raffle.
 *
 * @package Components\raffle
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_raffle/deleteraffle') )
	punt_user(null, pines_url('com_raffle', 'raffle/list'));

$list = explode(',', $_REQUEST['id']);
foreach ($list as $cur_raffle) {
	$cur_entity = com_raffle_raffle::factory((int) $cur_raffle);
	if ( !isset($cur_entity->guid) || !$cur_entity->delete() )
		$failed_deletes .= (empty($failed_deletes) ? '' : ', ').$cur_raffle;
}
if (empty($failed_deletes)) {
	pines_notice('Selected raffle(s) deleted successfully.');
} else {
	pines_error('Could not delete raffles with given IDs: '.$failed_deletes);
}

pines_redirect(pines_url('com_raffle', 'raffle/list'));

?>