<?php
/**
 * Provide a form to edit a company.
 *
 * @package Pines
 * @subpackage com_customer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zakhuber@gmail.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if (isset($_REQUEST['id'])) {
	if ( !gatekeeper('com_customer/editcompany') )
		punt_user('You don\'t have necessary permission.', pines_url('com_customer', 'editcompany', array('id' => $_REQUEST['id'])));
} else {
	if ( !gatekeeper('com_customer/newcompany') )
		punt_user('You don\'t have necessary permission.', pines_url('com_customer', 'editcompany'));
}

$entity = com_customer_company::factory((int) $_REQUEST['id']);
$entity->print_form();

?>