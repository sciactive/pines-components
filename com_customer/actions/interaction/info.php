<?php
/**
 * Retrieve customer interaction information, returning JSON.
 *
 * @package Components
 * @subpackage customer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_customer/viewhistory') )
	punt_user(null, pines_url('com_customer', 'interaction/info', $_REQUEST));

$pines->page->override = true;
header('Content-Type: application/json');

// Change the timezone to the supplied timezone or user's timezone.
$cur_timezone = date_default_timezone_get();
if (!empty($_REQUEST['timezone']))
	date_default_timezone_set($_REQUEST['timezone']);
else
	date_default_timezone_set($_SESSION['user']->get_timezone());

$interaction = com_customer_interaction::factory((int) $_REQUEST['id']);
if (!isset($interaction->guid))
	$pines->page->override_doc();

if (!isset($interaction->sale->guid)) {
	$sale_title = '';
	$sale_url = '';
} else {
	$sale_title = (count($interaction->sale->products) == 1) ? $interaction->sale->products[0]['entity']->name : count($interaction->sale->products).' items';
	$sale_url = pines_url('com_sales', 'sale/receipt', array('id' => $interaction->sale->guid));
}

if ($interaction->type == 'Email') {
	$contact_info = $interaction->customer->email;
} elseif (!empty($interaction->customer->phone_cell)) {
	$contact_info = format_phone($interaction->customer->phone_cell);
} elseif (!empty($interaction->customer->phone_home)) {
	$contact_info = format_phone($interaction->customer->phone_home);
} elseif (!empty($interaction->customer->phone_work)) {
	$contact_info = format_phone($interaction->customer->phone_work);
}

$json_struct = (object) array(
	'guid'				=> (int) $interaction->guid,
	'customer'			=> (string) $interaction->customer->name,
	'customer_url'		=> pines_url('com_customer', 'customer/edit', array('id' =>$interaction->customer->guid)),
	'sale'				=> $sale_title,
	'sale_url'			=> $sale_url,
	'employee'			=> (string) $interaction->employee->name,
	'type'				=> (string) $interaction->type,
	'contact_info'		=> $contact_info,
	'created_date'		=> format_date($interaction->p_cdate, 'full_sort'),
	'date'				=> format_date($interaction->action_date, 'full_sort'),
	'status'			=> (string) $interaction->status,
	'comments'			=> (string) $interaction->comments,
	'review_comments'	=> (array) $interaction->review_comments
);

$pines->page->override_doc(json_encode($json_struct));

date_default_timezone_set($cur_timezone);

?>