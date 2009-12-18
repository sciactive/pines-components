<?php
/**
 * Provide a form to create a new customer.
 *
 * @package Pines
 * @subpackage com_customer
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_customer/newcustomer') ) {
	$config->user_manager->punt_user("You don't have necessary permission.", pines_url('com_customer', 'newcustomer', null, false));
	return;
}

$config->run_customer->print_customer_form('com_customer', 'savecustomer');

?>