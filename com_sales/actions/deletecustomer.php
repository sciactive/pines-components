<?php
/**
 * Delete a customer.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/deletecustomer') ) {
	$config->user_manager->punt_user("You don't have necessary permission.", pines_url('com_sales', 'listcustomers', null, false));
	return;
}

$list = explode(',', $_REQUEST['id']);
foreach ($list as $cur_customer) {
	if ( !$config->run_sales->delete_customer($cur_customer) )
		$failed_deletes .= (empty($failed_deletes) ? '' : ', ').$cur_customer;
}
if (empty($failed_deletes)) {
	display_notice('Selected customer(s) deleted successfully.');
} else {
	display_error('Could not delete customers with given IDs: '.$failed_deletes);
}

$config->run_sales->list_customers();
?>