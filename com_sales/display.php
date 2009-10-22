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

if ( gatekeeper('com_sales/managemanufacturers') || gatekeeper('com_sales/newmanufacturer') ) {
	$com_sales_menu_id = $page->main_menu->add('POS');
	if ( gatekeeper('com_sales/managemanufacturers') )
		$page->main_menu->add('Manufacturers', pines_url('com_sales', 'listmanufacturers'), $com_sales_menu_id);
	if ( gatekeeper('com_sales/newmanufacturer') )
		$page->main_menu->add('New Manufacturer', pines_url('com_sales', 'newmanufacturer'), $com_sales_menu_id);
}

?>