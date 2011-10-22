<?php
/**
 * Delete a tax/fee.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/deletetaxfee') )
	punt_user(null, pines_url('com_sales', 'taxfee/list'));

$list = explode(',', $_REQUEST['id']);
foreach ($list as $cur_tax_fee) {
	$cur_entity = com_sales_tax_fee::factory((int) $cur_tax_fee);
	if ( !isset($cur_entity->guid) || !$cur_entity->delete() )
		$failed_deletes .= (empty($failed_deletes) ? '' : ', ').$cur_tax_fee;
}
if (empty($failed_deletes)) {
	pines_notice('Selected tax/fee(s) deleted successfully.');
} else {
	pines_error('Could not delete tax/fees with given IDs: '.$failed_deletes);
}

pines_redirect(pines_url('com_sales', 'taxfee/list'));

?>