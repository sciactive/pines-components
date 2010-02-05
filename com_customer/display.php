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

if ( gatekeeper('com_customer/listcustomers') || gatekeeper('com_customer/newcustomer') ||
	gatekeeper('com_customer/listcompanies') || gatekeeper('com_customer/newcompany')) {
	$com_customer_menu_id = $config->page->main_menu->add('CRM');
	if ( gatekeeper('com_customer/listcustomers') )
		$config->page->main_menu->add('Customers', pines_url('com_customer', 'listcustomers'), $com_customer_menu_id);
	if ( gatekeeper('com_customer/newcustomer') )
		$config->page->main_menu->add('New Customer', pines_url('com_customer', 'editcustomer'), $com_customer_menu_id);
	if (gatekeeper('com_customer/listcompanies') || gatekeeper('com_customer/newcompany')) {
		$com_customer_company_menu_id = $config->page->main_menu->add('Companies', '#', $com_customer_menu_id);
		if ( gatekeeper('com_customer/listcompanies') )
			$config->page->main_menu->add('Companies', pines_url('com_customer', 'listcompanies'), $com_customer_company_menu_id);
		if ( gatekeeper('com_customer/newcompany') )
			$config->page->main_menu->add('New Company', pines_url('com_customer', 'editcompany'), $com_customer_company_menu_id);
	}
}

?>