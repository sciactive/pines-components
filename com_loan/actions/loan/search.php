<?php
/**
 * Action to search for loans
 *
 * @package Components\loan
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <angela@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_loan/listloans') )
	punt_user(null, pines_url('com_loan', 'loan/list'));

$pines->page->override = true;
header('Content-Type: application/json');

// Time span.
if (!empty($_REQUEST['start_date'])) {
	$start_date = $_REQUEST['start_date'];
	if (strpos($start_date, '-') === false)
		$start_date = format_date($start_date, 'date_sort');
	$start_date = strtotime($start_date.' 00:00:00');
}
if (!empty($_REQUEST['end_date'])) {
	$end_date = $_REQUEST['end_date'];
	if (strpos($end_date, '-') === false)
		$end_date = format_date($end_date, 'date_sort');
	$end_date = strtotime($end_date.' 23:59:59') + 1;
}
if ($_REQUEST['all_time'] == 'true') {
	$start_date = null;
	$end_date = null;
}
if (!empty($_REQUEST['location']))
	$location = group::factory((int) $_REQUEST['location']);

$descendants = ($_REQUEST['descendants'] == 'true');

// The query.
$query = trim($_REQUEST['q']);


// Build the main selector.
$selector = array('&', 'tag' => array('com_loan', 'loan'));
if (isset($start_date))
	$selector['gte'] = array('p_cdate', (int) $start_date);
if (isset($end_date))
	$selector['lt'] = array('p_cdate', (int) $end_date);
if (isset($location)) {
	if ($descendants)
		$or = array('|', 'ref' => array('group', $location->get_descendants(true)));
	else
		$or = array('|', 'ref' => array('group', $location));
}

if ($_REQUEST['status_tags']) {
	$status_tags = explode(',',$_REQUEST['status_tags']);
	
	if (isset($or)) {
		$or['tag'] = $status_tags;
	} else {
		$or = array('|', 'tag' => $status_tags);
	}
} 

// Determine the type of query.
if (preg_match('/^\s*$/', $query)) {
	// Nothing was queried.
	$loans = array();
} elseif ($query == '*') {
	// The user wants to see all applicaple loans.
	if (!gatekeeper('com_loan/listallloans'))
		$loans = array();
	else {
		$args = array(
				array('class' => com_loan_loan, 'reverse' => true),
				$selector
			);
		if ($or)
			$args[] = $or;
		$loans = (array) call_user_func_array(array($pines->entity_manager, 'get_entities'), $args);
	}
} else {
	if ($loan_id_match = preg_match_all('/loan:(\d+)/', $query, $loan_ids))
		$query = trim(preg_replace('/loan:(\d+)/', '', $query));
	$r_query = '/'.str_replace(' ', '.*', preg_quote($query)).'/i';
	if (!preg_match('/^\s*$/', $query)) {
		$selector2 = array('|',
				'match' => array(
					array('name', $r_query),
					array('email', $r_query)
				)
			);
		$num_query = preg_replace('/\D/', '', $query);
		$r_num_query = '/'.preg_quote($num_query).'/';
		if ($num_query != '') {
			$selector2['match'][] = array('phone', $r_num_query);
			$selector2['match'][] = array('phone_cell', $r_num_query);
		}
	} elseif ($loan_id_match) {
		$selector2 = array('|',
				'strict' => array()
			);
		foreach ($loan_ids[1] as $cur_loan_id)
			$selector2['strict'][] = array('id', (int) $cur_loan_id);
	}
	// Only search for loans if it's a normal search.
	if (!isset($loans)) {
		$args = array(
				array('class' => com_loan_loan, 'reverse' => true, 'limit' => $pines->config->com_loan->loan_search_limit),
				$selector
			);
		if ($or)
			$args[] = $or;
		if ($selector2)
			$args[] = $selector2;
		$loans = (array) call_user_func_array(array($pines->entity_manager, 'get_entities'), $args);
	}
}

// Don't bother if apps weren't requested.
if ($loans) {
	foreach ($loans as $key => &$cur_loan) {
		
		if ($_REQUEST['refresh_payment_info'] === 'true') {
			$cur_loan->get_payments_array();
			// It's okay if the paid array previously existed, that will give us an
			// accurate payments array, which is what we need.
			if ($cur_loan->payments[0]['unpaid_balance'] > 0)
				$cur_loan->unpaid_balance = $cur_loan->payments[0]['unpaid_balance'];
			else
				$cur_loan->unpaid_balance = 0.00;

			if ($cur_loan->payments[0]['unpaid_interest'] > 0)
				$cur_loan->unpaid_interest = $cur_loan->payments[0]['unpaid_interest'];
			else
				$cur_loan->unpaid_interest = 0.00;

			$past_due = $cur_loan->payments[0]['past_due'] - ($cur_loan->payments[0]['unpaid_balance_not_past_due'] + $cur_loan->payments[0]['unpaid_interest_not_past_due']);
			if ($past_due > 0)
				$cur_loan->past_due = $past_due;
			else
				$cur_loan->past_due = 0.00;

			$num = count($cur_loan->paid) - 1;

			if ($cur_loan->paid[$num]['payment_status'] == 'partial_not_due') {
				$cur_loan->past_due = null;
				$cur_loan->balance = $cur_loan->payments[0]['next_payment_due_amount'];
			} else
				$cur_loan->balance = $cur_loan->past_due + $cur_loan->payments[0]['next_payment_due_amount'];

			$cur_loan->save();
		}
		$due_date = $cur_loan->first_payment_date;
		$today = strtotime('now');
		$days = format_date_range($due_date, $today, '#days#');
		$missed_first_payment = ( $due_date < $today  && empty($cur_loan->paid)) ? $days : false;
		
		// Figure out Status
		$cur_status = $cur_loan->get_loan_status();
		$archived = ($cur_status != 'Active') ? $cur_status : false;
		
		// Next due:
		if ($cur_loan->status == 'paid off')
			$next_due_payment = 'Paid Off';
		else if (isset($cur_loan->payments[0]['next_payment_due']))
			$next_due_payment = format_date($cur_loan->payments[0]['next_payment_due'], "date_short");
		else
			$next_due_payment = format_date($cur_loan->first_payment_date, "date_short");
		
		$json_struct = (object) array(
			'guid'					=> $cur_loan->guid,
			'id'					=> (int) $cur_loan->id,
			'customer_name'			=> htmlspecialchars($cur_loan->name),
			'customer_guid'			=> htmlspecialchars($cur_loan->customer->guid),
			'employee'				=> htmlspecialchars($cur_loan->user->name),
			'employee_guid'			=> htmlspecialchars($cur_loan->user->guid),
			'location_guid'			=> htmlspecialchars($cur_loan->group->guid),
			'location'				=> htmlspecialchars($cur_loan->group->name),
			'creation_date'			=> htmlspecialchars(format_date($cur_loan->creation_date, "date_short")),
			'status'				=> htmlspecialchars($cur_status),
			'archived'				=> htmlspecialchars($archived),
			'collection_code'		=> (isset($cur_loan->collection_code)) ? htmlspecialchars($cur_loan->collection_code) : '',
			'principal'				=> "$".htmlspecialchars($pines->com_sales->round($cur_loan->principal, true)),
			'term'					=> htmlspecialchars($cur_loan->term." ".$cur_loan->term_type),
			'apr'					=> htmlspecialchars($cur_loan->apr)."%",
			'balance'				=> !isset($cur_loan->remaining_balance) ? "$".htmlspecialchars($pines->com_sales->round($cur_loan->principal, true)): '$'.htmlspecialchars($pines->com_sales->round($cur_loan->remaining_balance, true)),
			'payment'				=> "$".htmlspecialchars($pines->com_sales->round($cur_loan->frequency_payment, true)),
			'missed_first_payment'	=> htmlspecialchars($missed_first_payment),
			'next_payment_amount'	=> "$".htmlspecialchars($pines->com_sales->round($cur_loan->payments[0]['next_payment_due_amount'], true)),
			'current_past_due'		=> ($cur_loan->payments[0]['past_due'] < .01) ? "$0.00" : '$'.htmlspecialchars($pines->com_sales->round($cur_loan->payments[0]['past_due'], true)),
			'next_payment_due'		=> htmlspecialchars($next_due_payment),
			'total_payments_made'	=> empty($cur_loan->payments[0]['total_interest_paid']) ? "$0.00" : '$'.htmlspecialchars($pines->com_sales->round(($cur_loan->payments[0]['total_principal_paid'] + $cur_loan->payments[0]['total_interest_paid']), true)),
			'total_principal_paid'	=> empty($cur_loan->payments[0]['total_principal_paid']) ? "$0.00" : '$'.htmlspecialchars($pines->com_sales->round($cur_loan->payments[0]['total_principal_paid'], true)),
			'total_interest_paid'	=> empty($cur_loan->payments[0]['total_interest_paid']) ? "$0.00" : '$'.htmlspecialchars($pines->com_sales->round($cur_loan->payments[0]['total_interest_paid'], true)),
		);
		$cur_loan = $json_struct;
	}
	unset($cur_loan);

	if (!$loans)
		$loans = null;
}

$pines->page->override_doc(json_encode($loans));
?>