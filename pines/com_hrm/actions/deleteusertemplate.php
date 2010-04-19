<?php
/**
 * Delete a user template.
 *
 * @package Pines
 * @subpackage com_hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_hrm/deleteusertemplate') )
	punt_user('You don\'t have necessary permission.', pines_url('com_hrm', 'listusertemplates'));

$list = explode(',', $_REQUEST['id']);
foreach ($list as $cur_user_template) {
	$cur_entity = com_hrm_user_template::factory((int) $cur_user_template);
	if ( !isset($cur_entity->guid) || !$cur_entity->delete() )
		$failed_deletes .= (empty($failed_deletes) ? '' : ', ').$cur_user_template;
}
if (empty($failed_deletes)) {
	pines_notice('Selected user template(s) deleted successfully.');
} else {
	pines_error('Could not delete user templates with given IDs: '.$failed_deletes);
}

$pines->com_hrm->list_user_templates();
?>