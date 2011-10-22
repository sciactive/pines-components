<?php
/**
 * Delete a condition.
 *
 * @package Pines
 * @subpackage com_configure
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_configure/edit') )
	punt_user(null, pines_url('com_configure', 'condition/list'));

$list = explode(',', $_REQUEST['id']);
foreach ($list as $cur_condition) {
	$cur_entity = com_configure_condition::factory((int) $cur_condition);
	if ( !isset($cur_entity->guid) || !$cur_entity->delete() )
		$failed_deletes .= (empty($failed_deletes) ? '' : ', ').$cur_condition;
}
if (empty($failed_deletes)) {
	pines_notice('Selected condition(s) deleted successfully.');
} else {
	pines_error('Could not delete categories with given IDs: '.$failed_deletes."\n\nThey may have already been deleted.");
}

pines_redirect(pines_url('com_configure', 'list', array('percondition' => '1')));

?>