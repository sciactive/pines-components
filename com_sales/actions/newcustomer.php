<?php
/**
 * Provide a form to create a new customer.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/newcustomer') ) {
	$config->user_manager->punt_user("You don't have necessary permission.", pines_url('com_sales', 'newcustomer', null, false));
	return;
}

$entity = new com_sales_customer;
$entity->print_form();

?>