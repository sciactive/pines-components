<?php
/**
 * Provide a form for a payment process type to collect information.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ($_REQUEST['type'] == 'return') {
	if ( isset($_REQUEST['id']) ) {
		if ( !gatekeeper('com_sales/editreturn') )
			punt_user('You don\'t have necessary permission.', pines_url('com_sales', 'sale/list'));
		$ticket = com_sales_return::factory((int) $_REQUEST['id']);
	} else {
		if ( !gatekeeper('com_sales/newreturn') && !gatekeeper('com_sales/newreturnwsale') )
			punt_user('You don\'t have necessary permission.', pines_url('com_sales', 'sale/list'));
		$ticket = com_sales_return::factory();
	}
} else {
	if ( isset($_REQUEST['id']) ) {
		if ( !gatekeeper('com_sales/editsale') )
			punt_user('You don\'t have necessary permission.', pines_url('com_sales', 'sale/list'));
		$ticket = com_sales_sale::factory((int) $_REQUEST['id']);
	} else {
		if ( !gatekeeper('com_sales/newsale') )
			punt_user('You don\'t have necessary permission.', pines_url('com_sales', 'sale/list'));
		$ticket = com_sales_sale::factory();
	}
}

if ($pines->config->com_sales->com_customer && $ticket->status != 'invoiced' && $ticket->status != 'paid' && $ticket->status != 'processed' && $ticket->status != 'voided') {
	$ticket->customer = null;
	if (preg_match('/^\d+/', $_REQUEST['customer'])) {
		$ticket->customer = com_customer_customer::factory((int) $_REQUEST['customer']);
		if (!isset($ticket->customer->guid))
			$ticket->customer = null;
	}
}

$pines->page->override = true;
$pines->com_sales->call_payment_process(array(
	'action' => 'request',
	'name' => $_REQUEST['name'],
	'ticket' => $ticket
), $module);

if (isset($module))
	$pines->page->override_doc($module->render());

?>