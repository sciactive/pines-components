<?php
/**
 * Delete a return.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/deletereturn') )
	punt_user(null, pines_url('com_sales', 'return/list'));

$list = explode(',', $_REQUEST['id']);
foreach ($list as $cur_return) {
	$cur_entity = com_sales_return::factory((int) $cur_return);
	if ( !isset($cur_entity->guid) || !$cur_entity->delete() )
		$failed_deletes .= (empty($failed_deletes) ? '' : ', ').$cur_return;
}
if (empty($failed_deletes)) {
	pines_notice('Selected return(s) deleted successfully.');
} else {
	pines_error('Could not delete returns with given IDs: '.$failed_deletes);
}

redirect(pines_url('com_sales', 'return/list'));

?>