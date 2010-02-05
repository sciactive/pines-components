<?php
/**
 * Delete a shipper.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/deleteshipper') )
	punt_user('You don\'t have necessary permission.', pines_url('com_sales', 'listshippers', null, false));

$list = explode(',', $_REQUEST['id']);
foreach ($list as $cur_shipper) {
	$cur_entity = com_sales_shipper::factory((int) $cur_shipper);
	if ( is_null($cur_entity->guid) || !$cur_entity->delete() )
		$failed_deletes .= (empty($failed_deletes) ? '' : ', ').$cur_shipper;
}
if (empty($failed_deletes)) {
	display_notice('Selected shipper(s) deleted successfully.');
} else {
	display_error('Could not delete shippers with given IDs: '.$failed_deletes);
}

$pines->run_sales->list_shippers();
?>