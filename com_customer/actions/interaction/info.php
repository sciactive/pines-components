<?php
/**
 * Retreive customer interaction information, returning JSON.
 *
 * @package Pines
 * @subpackage com_customer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_customer/viewhistory') )
	punt_user(null, pines_url('com_customer', 'interaction/info', $_REQUEST));

$pines->page->override = true;

$interaction = com_customer_interaction::factory((int) $_REQUEST['id']);
if (!isset($interaction->guid))
	$pines->page->override_doc();

if (!isset($interaction->sale->guid)) {
	$sale_url = '';
} else {
	$sale_title = (count($interaction->sale->products) == 1) ? htmlspecialchars($interaction->sale->products[0]['entity']->name) : count($interaction->sale->products).' items';
	$sale_url = '<a href="'.htmlspecialchars(pines_url('com_sales', 'sale/receipt', array('id' => $interaction->sale->guid))).'" onclick="window.open(this.href); return false;">'.htmlspecialchars($sale_title).'</a>';
}

$json_struct = (object) array(
	'guid'				=> (int) $interaction->guid,
	'customer'			=> (string) $interaction->customer->name,
	'customer_url'		=> pines_url('com_customer', 'customer/edit', array('id' =>$interaction->customer->guid)),
	'sale_url'			=> $sale_url,
	'employee'			=> (string) $interaction->employee->name,
	'type'				=> (string) $interaction->type,
	'contact_info'		=> ($interaction->type == 'Email') ? htmlspecialchars($interaction->customer->email) : format_phone($interaction->customer->phone_cell),
	'date'				=> format_date($interaction->action_date, 'full_sort'),
	'status'			=> (string) $interaction->status,
	'comments'			=> (string) $interaction->comments,
	'review_comments'	=> (array) $interaction->review_comments
);

$pines->page->override_doc(json_encode($json_struct));

?>