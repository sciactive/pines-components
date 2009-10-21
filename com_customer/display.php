<?php
/**
 * com_customer's display control.
 *
 * @package Pines
 * @subpackage com_customer
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( gatekeeper('com_customer/managecustomers') || gatekeeper('com_customer/new') ) {
	$com_customer_menu_id = $page->main_menu->add('Customers');
	if ( gatekeeper('com_customer/managecustomers') )
		$page->main_menu->add('Customers', pines_url('com_customer', 'listcustomers'), $com_customer_menu_id);
	if ( gatekeeper('com_customer/new') )
		$page->main_menu->add('New Customer', pines_url('com_customer', 'newcustomer'), $com_customer_menu_id);
}

?>