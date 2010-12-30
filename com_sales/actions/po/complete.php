<?php
/**
 * Complete a PO.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/completepo') )
	punt_user(null, pines_url('com_sales', 'po/list'));

$list = explode(',', $_REQUEST['id']);
foreach ($list as $cur_po) {
	$cur_entity = com_sales_po::factory((int) $cur_po);
	$cur_entity->finished = true;
	if ( !isset($cur_entity->guid) || !$cur_entity->save() )
		$failed_completes .= (empty($failed_completes) ? '' : ', ').$cur_po;
}
if (empty($failed_completes)) {
	pines_notice('Selected PO(s) marked as completed.');
} else {
	pines_error('Could not complete POs with given IDs: '.$failed_completes);
}

redirect(pines_url('com_sales', 'po/list'));

?>