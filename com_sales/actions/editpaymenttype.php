<?php
/**
 * Provide a form to edit a payment type.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/editpaymenttype') ) {
	$config->user_manager->punt_user("You don't have necessary permission.", pines_url('com_sales', 'editpaymenttype', array('id' => $_REQUEST['id']), false));
	return;
}

$config->run_sales->print_payment_type_form('com_sales', 'savepaymenttype', $_REQUEST['id']);

?>