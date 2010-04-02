<?php
/**
 * Delete a customer.
 *
 * @package Pines
 * @subpackage com_customer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_customer/deletecustomer') )
	punt_user('You don\'t have necessary permission.', pines_url('com_customer', 'listcustomers', null, false));

$list = explode(',', $_REQUEST['id']);
foreach ($list as $cur_customer) {
	$cur_entity = com_customer_customer::factory((int) $cur_customer);
	if ( is_null($cur_entity->guid) || !$cur_entity->delete() )
		$failed_deletes .= (empty($failed_deletes) ? '' : ', ').$cur_customer;
}
if (empty($failed_deletes)) {
	pines_notice('Selected customer(s) deleted successfully.');
} else {
	pines_error('Could not delete customers with given IDs: '.$failed_deletes);
}

$pines->com_customer->list_customers();
?>