<?php
/**
 * Provide a form to edit a payment type.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if (isset($_REQUEST['id'])) {
	if ( !gatekeeper('com_sales/editpaymenttype') )
		punt_user('You don\'t have necessary permission.', pines_url('com_sales', 'editpaymenttype', array('id' => $_REQUEST['id'])));
} else {
	if ( !gatekeeper('com_sales/newpaymenttype') )
		punt_user('You don\'t have necessary permission.', pines_url('com_sales', 'editpaymenttype'));
}

$entity = com_sales_payment_type::factory((int) $_REQUEST['id']);
$entity->print_form();

?>