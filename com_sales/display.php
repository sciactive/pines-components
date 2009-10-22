<?php
/**
 * com_sales's display control.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( gatekeeper('com_sales/managecustomers') || gatekeeper('com_sales/new') ) {
	$com_sales_menu_id = $page->main_menu->add('Customers');
	if ( gatekeeper('com_sales/managecustomers') )
		$page->main_menu->add('Customers', pines_url('com_sales', 'listcustomers'), $com_sales_menu_id);
	if ( gatekeeper('com_sales/new') )
		$page->main_menu->add('New Customer', pines_url('com_sales', 'newcustomer'), $com_sales_menu_id);
}

?>