<?php
/**
 * Return sales total JSON.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/totalsales') )
	punt_user(null, pines_url('com_sales', 'sale/totalsjson', $_REQUEST));

$pines->page->override = true;

// Format the location.
$location = group::factory((int) $_REQUEST['location']);
if (!isset($location->guid))
	$location = $_SESSION['user']->group;

// Format the date.
if (preg_match('/\d{4}-\d{2}-\d{2}/', $_REQUEST['date_start'])) {
	$date_start = strtotime($_REQUEST['date_start'].' 00:00:00');
} else {
	$date_start = strtotime('00:00:00');
}
if (preg_match('/\d{4}-\d{2}-\d{2}/', $_REQUEST['date_end'])) {
	$date_end = strtotime($_REQUEST['date_end'].' 23:59:59');
} else {
	$date_end = strtotime('23:59:59');
}

// Build the entity query.
$selector = array('&',
	'tag' => array('com_sales', 'transaction'),
	'gte' => array('p_cdate', $date_start),
	'lte' => array('p_cdate', $date_end)
);
$or = array('|', 'ref' => array('group', $location->get_descendents(true)));

// Get all transactions.
$tx_array = (array) $pines->entity_manager->get_entities(array('class' => com_sales_tx, 'skip_ac' => true),  array('|', 'tag' => array('sale_tx', 'payment_tx')), $selector, $or);
$invoice_array = array('total' => 0.00, 'count' => 0);
$sale_array = array('total' => 0.00, 'count' => 0);
$sale_array_user = array();
$return_array = array('total' => 0.00, 'count' => 0);
$return_array_user = array();
$payment_array = array();
$return_payment_array = array();
$total_array = array('total' => 0.00);
$total_array_user = array();
$total_payment_array = array();

// Total the sales.
foreach ($tx_array as $key => $cur_tx) {
	// Skip voided sales.
	if ($cur_tx->ticket->status == 'voided')
		continue;
	if ($cur_tx->has_tag('sale_tx')) {
		$total = (float) $cur_tx->ticket->total;
		$name = "{$cur_tx->user->name} [{$cur_tx->user->username}]";
		switch ($cur_tx->type) {
			case 'invoiced':
				// Check if the sale is still invoiced.
				if ($cur_tx->ticket->status == 'invoiced') {
					$invoice_array['total'] += $total;
					$invoice_array['count']++;
				}
				break;
			case 'paid':
				$total_array['total'] += $total;
				$sale_array['total'] += $total;
				$sale_array['count']++;
				$total_array_user[$name]['total'] += $total;
				$sale_array_user[$name]['total'] += $total;
				$sale_array_user[$name]['count']++;
				break;
			case 'returned':
				$total_array['total'] -= $total;
				$return_array['total'] += $total;
				$return_array['count']++;
				$total_array_user[$name]['total'] -= $total;
				$return_array_user[$name]['total'] += $total;
				$return_array_user[$name]['count']++;
				break;
		}
	} else {
		$amount = (float) $cur_tx->amount;
		$name = $cur_tx->ref->name;
		switch ($cur_tx->type) {
			case 'payment_received':
				$total_payment_array[$name]['total'] += $amount;
				$payment_array[$name]['total'] += $amount;
				$payment_array[$name]['net_total'] += $amount;
				$payment_array[$name]['count']++;
				break;
			case 'change_given':
				$total_payment_array[$name]['total'] -= $amount;
				$payment_array[$name]['change_given'] += $amount;
				$payment_array[$name]['net_total'] -= $amount;
				break;
			case 'payment_returned':
				$total_payment_array[$name]['total'] -= $amount;
				$return_payment_array[$name]['total'] += $amount;
				$return_payment_array[$name]['net_total'] += $amount;
				$return_payment_array[$name]['count']++;
				break;
		}
	}
}

if (empty($tx_array)) {
	$return = null;
} else {
	if (is_int($location)) {
		$location = group::factory($location);
		$location = "{$location->name} [{$location->groupname}]";
	}
	$return = array(
		'location' => $location,
		'date_start' => date('Y-m-d', $date_start),
		'date_end' => date('Y-m-d', $date_end),
		'invoices' => $invoice_array,
		'sales' => $sale_array,
		'sales_user' => $sale_array_user,
		'returns' => $return_array,
		'returns_user' => $return_array_user,
		'payments' => $payment_array,
		'returns_payments' => $return_payment_array,
		'totals' => $total_array,
		'totals_user' => $total_array_user,
		'totals_payments' => $total_payment_array
	);
}

$pines->page->override_doc(json_encode($return));

?>