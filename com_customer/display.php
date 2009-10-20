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

if ( gatekeeper('com_customer/new') ) {
	$com_customer_menu_id = $page->main_menu->add('Customers');
	if ( gatekeeper('com_user/managecustomers') )
		$page->main_menu->add('Customers', $config->template->url('com_customer', 'listcustomers'), $com_customer_menu_id);
	if ( gatekeeper('com_user/new') )
		$page->main_menu->add('New Customer', $config->template->url('com_customer', 'newcustomer'), $com_customer_menu_id);
}

?>