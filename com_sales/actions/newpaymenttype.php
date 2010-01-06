<?php
/**
 * Provide a form to create a new payment type.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/newpaymenttype') ) {
	$config->user_manager->punt_user("You don't have necessary permission.", pines_url('com_sales', 'newpaymenttype', null, false));
	return;
}

$entity = new com_sales_payment_type;
$entity->print_form();

?>