<?php
/**
 * Provide a form to edit a customer.
 *
 * @package Components
 * @subpackage customer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if (!empty($_REQUEST['id'])) {
	if ( !gatekeeper('com_customer/editcustomer') )
		punt_user(null, pines_url('com_customer', 'customer/edit', array('id' => $_REQUEST['id'])));
} else {
	if ( !gatekeeper('com_customer/newcustomer') )
		punt_user(null, pines_url('com_customer', 'customer/edit'));
}

$entity = com_customer_customer::factory((int) $_REQUEST['id']);
$entity->print_form();

?>