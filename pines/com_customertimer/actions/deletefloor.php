<?php
/**
 * Delete a floor.
 *
 * @package Pines
 * @subpackage com_customertimer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_customertimer/deletefloor') )
	punt_user('You don\'t have necessary permission.', pines_url('com_customertimer', 'listfloors'));

$list = explode(',', $_REQUEST['id']);
foreach ($list as $cur_floor) {
	$cur_entity = com_customertimer_floor::factory((int) $cur_floor);
	if ( is_null($cur_entity->guid) || !$cur_entity->delete() )
		$failed_deletes .= (empty($failed_deletes) ? '' : ', ').$cur_floor;
}
if (empty($failed_deletes)) {
	pines_notice('Selected floor(s) deleted successfully.');
} else {
	pines_error('Could not delete floors with given IDs: '.$failed_deletes);
}

$pines->com_customertimer->list_floors();
?>