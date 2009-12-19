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

if ( gatekeeper('com_customer/managecustomers') ) {
	$com_customer_menu_id = $page->main_menu->add('Customer Accounts', pines_url('com_customer', 'listcustomers'));
}

?>