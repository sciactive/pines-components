<?php
/**
 * com_customer_timer's display control.
 *
 * @package Pines
 * @subpackage com_customer_timer
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( gatekeeper('com_customer_timer/viewstatus') || gatekeeper('com_customer_timer/login') ) {
	$com_customer_timer_menu_id = $pines->page->main_menu->add('Customer Timer', '#', $com_customer_menu_id);
	if ( gatekeeper('com_customer_timer/viewstatus') )
		$pines->page->main_menu->add('Status', pines_url('com_customer_timer', 'status'), $com_customer_timer_menu_id);
	if ( gatekeeper('com_customer_timer/login') )
		$pines->page->main_menu->add('Login', pines_url('com_customer_timer', 'login'), $com_customer_timer_menu_id);
}

//if (gatekeeper('com_customer_timer/viewstatus'))
//	$com_customer_timer_module = new module('com_customer_timer', 'status_check', 'head');

?>