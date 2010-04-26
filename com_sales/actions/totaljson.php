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
	punt_user('You don\'t have necessary permission.', pines_url('com_sales', 'totaljson', $_REQUEST));

$pines->page->override = true;

// Format the location.
$location = $_REQUEST['location'];
if (!isset($location) || $location == 'current') {
	$location = 'current';
} elseif ($location == 'all') {
	if (!gatekeeper('com_sales/totalothersales')) {
		$pines->page->override_doc('false');
		return;
	}
} else {
	if (!gatekeeper('com_sales/totalothersales')) {
		$pines->page->override_doc('false');
		return;
	}
	$location = (int) $_REQUEST['location'];
}

// Format the date.
if (preg_match('/\d{4}-\d{2}-\d{2}/', $_REQUEST['date_start'])) {
	$date_start = strtotime($_REQUEST['date_start'].' 00:00:00');
} else {
	$date_start = strtotime(date('Y-m-d 00:00:00'));
}
if (preg_match('/\d{4}-\d{2}-\d{2}/', $_REQUEST['date_end'])) {
	$date_end = strtotime($_REQUEST['date_end'].' 23:59:59');
} else {
	$date_end = strtotime(date('Y-m-d 23:59:59'));
}

// Get all transactions.
$tx_array = $pines->entity_manager->get_entities(array(
		'tags' => array('com_sales', 'transaction'),
		'tags_i' => array('sale_tx', 'payment_tx'),
		'class' => com_sales_tx));
if (!is_array($tx_array))
	$tx_array = array();
$invoice_array = array('total' => 0.00, 'count' => 0);
$sale_array = array('total' => 0.00, 'count' => 0);
$user_array = array();
$payment_array = array();

// Total the sales.
foreach ($tx_array as $key => &$cur_tx) {
	if ($cur_tx->p_cdate >= $date_start &&
		$cur_tx->p_cdate <= $date_end &&
		(
			$location == 'all' ||
			($location == 'current' && $_SESSION['user']->in_group($cur_tx->gid)) ||
			$location == $cur_tx->gid
		)
		) {
		if ($cur_tx->has_tag('sale_tx')) {
			if ($cur_tx->type == 'invoiced') {
				$invoice_array['total'] += (float) $cur_tx->ticket->total;
				$invoice_array['count']++;
			} elseif ($cur_tx->type == 'paid') {
				$invoice_array['total'] -= (float) $cur_tx->ticket->total;
				$invoice_array['count']--;
				$sale_array['total'] += (float) $cur_tx->ticket->total;
				$sale_array['count']++;
				$user_array["{$cur_tx->user->guid}: {$cur_tx->user->name} [{$cur_tx->user->username}]"]['total'] += (float) $cur_tx->ticket->total;
				$user_array["{$cur_tx->user->guid}: {$cur_tx->user->name} [{$cur_tx->user->username}]"]['count']++;
			}
		} elseif ($cur_tx->has_tag('payment_tx')) {
			if ($cur_tx->type == 'payment_received') {
				$payment_array[$cur_tx->ref->name]['total'] += (float) $cur_tx->amount;
				$payment_array[$cur_tx->ref->name]['net_total'] += (float) $cur_tx->amount;
				$payment_array[$cur_tx->ref->name]['count']++;
			} elseif ($cur_tx->type == 'change_given') {
				$payment_array[$cur_tx->ref->name]['change_given'] += (float) $cur_tx->amount;
				$payment_array[$cur_tx->ref->name]['net_total'] -= (float) $cur_tx->amount;
			}
		}
	} else {
		unset($tx_array[$key]);
	}
}

if (empty($tx_array)) {
	$return = null;
} else {
	$return = array(
		'location' => is_int($location) ? group::factory($location)->groupname : $location,
		'date_start' => date('Y-m-d', $date_start),
		'date_end' => date('Y-m-d', $date_end),
		'invoice' => $invoice_array,
		'sale' => $sale_array,
		'user' => $user_array,
		'payment' => $payment_array
	);
}

$pines->page->override_doc(json_encode($return));

?>