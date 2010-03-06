<?php
/**
 * Add menu entries.
 *
 * @package Pines
 * @subpackage com_customertimer
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( gatekeeper('com_customertimer/viewstatus') || gatekeeper('com_customertimer/login') ) {
	$com_customertimer_menu_id = $pines->page->main_menu->add('Customer Timer', '#', $com_customer_menu_id);
	if ( gatekeeper('com_customertimer/viewstatus') )
		$pines->page->main_menu->add('Status', pines_url('com_customertimer', 'status'), $com_customertimer_menu_id);
	if ( gatekeeper('com_customertimer/login') )
		$pines->page->main_menu->add('Login', pines_url('com_customertimer', 'login'), $com_customertimer_menu_id);
}

?>