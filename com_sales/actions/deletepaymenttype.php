<?php
/**
 * Delete a payment type.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/deletepaymenttype') )
	punt_user('You don\'t have necessary permission.', pines_url('com_sales', 'listpaymenttypes', null, false));

$list = explode(',', $_REQUEST['id']);
foreach ($list as $cur_payment_type) {
	$cur_entity = com_sales_payment_type::factory((int) $cur_payment_type);
	if ( is_null($cur_entity->guid) || !$cur_entity->delete() )
		$failed_deletes .= (empty($failed_deletes) ? '' : ', ').$cur_payment_type;
}
if (empty($failed_deletes)) {
	display_notice('Selected payment type(s) deleted successfully.');
} else {
	display_error('Could not delete payment types with given IDs: '.$failed_deletes);
}

$pines->run_sales->list_payment_types();
?>