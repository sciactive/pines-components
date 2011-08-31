<?php
/**
 * Delete a return checklist.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/deletereturnchecklist') )
	punt_user(null, pines_url('com_sales', 'returnchecklist/list'));

$list = explode(',', $_REQUEST['id']);
foreach ($list as $cur_return_checklist) {
	$cur_entity = com_sales_return_checklist::factory((int) $cur_return_checklist);
	if ( !isset($cur_entity->guid) || !$cur_entity->delete() )
		$failed_deletes .= (empty($failed_deletes) ? '' : ', ').$cur_return_checklist;
}
if (empty($failed_deletes)) {
	pines_notice('Selected return checklist(s) deleted successfully.');
} else {
	pines_error('Could not delete return checklists with given IDs: '.$failed_deletes);
}

pines_redirect(pines_url('com_sales', 'returnchecklist/list'));

?>