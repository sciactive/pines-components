<?php
/**
 * Delete a sale.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/deletesale') )
	punt_user(null, pines_url('com_sales', 'sale/list'));

$list = explode(',', $_REQUEST['id']);
foreach ($list as $cur_sale) {
	$cur_entity = com_sales_sale::factory((int) $cur_sale);
	if ( !isset($cur_entity->guid) || !$cur_entity->delete() )
		$failed_deletes .= (empty($failed_deletes) ? '' : ', ').$cur_sale;
}
if (empty($failed_deletes)) {
	pines_notice('Selected sale(s) deleted successfully.');
} else {
	pines_error('Could not delete sales with given IDs: '.$failed_deletes);
}

redirect(pines_url('com_sales', 'sale/list'));

?>