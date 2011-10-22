<?php
/**
 * Delete a cash count.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/deletecashcounts') )
	punt_user(null, pines_url('com_sales', 'cashcount/list'));

$list = explode(',', $_REQUEST['id']);
foreach ($list as $cur_sheet) {
	$cur_entity = com_sales_cashcount::factory((int) $cur_sheet);
	//Should we have it delete all audits as well?
	if ( !isset($cur_entity->guid) || !$cur_entity->delete() )
		$failed_deletes .= (empty($failed_deletes) ? '' : ', ').$cur_sheet;
}
if (empty($failed_deletes)) {
	pines_notice('Selected cash count(s) deleted successfully.');
} else {
	pines_error('Could not delete cash counts with given IDs: '.$failed_deletes);
	pines_notice('Note that cash counts cannot be deleted after items have been received on them.');
}

pines_redirect(pines_url('com_sales', 'cashcount/list'));

?>