<?php
/**
 * Search customers, returning JSON.
 *
 * @package Components
 * @subpackage customer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_customer/listcustomers') )
	punt_user(null, pines_url('com_customer', 'customer/search', $_REQUEST));

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

$query = trim($_REQUEST['q']);

// Build the main selector, including location and timespan.
$selector = array('&', 'tag' => array('com_customer', 'customer'));
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


if (empty($query)) {
	$customers = array();
} elseif ($query == '*') {
	if (!gatekeeper('com_customer/listallcustomers'))
		$customers = array();
	else {
		$args = array(
			array('class' => com_customer_customer),
			$selector
		);
		if ($or)
			$args[] = $or;
		$customers = (array) call_user_func_array(array($pines->entity_manager, 'get_entities'), $args);
	}
} else {
	$num_query = preg_replace('/\D/', '', $query);
	$r_query = '/'.str_replace(' ', '.*', preg_quote($query)).'/i';
	$r_num_query = '/'.preg_quote($num_query).'/';
	$selector2 = array('|',
		'match' => array(
			array('name', $r_query),
			array('username', $r_query),
			array('email', $r_query),
			array('referral_code', $r_query)
		)
	);
	if ($num_query != '') {
		$selector2['match'][] = array('phone_home', $r_num_query);
		$selector2['match'][] = array('phone_work', $r_num_query);
		$selector2['match'][] = array('phone_cell', $r_num_query);
		$selector2['match'][] = array('fax', $r_num_query);
	}
	$args = array(
			array('class' => com_customer_customer, 'limit' => $pines->config->com_customer->customer_search_limit),
			$selector,
			$selector2
		);
	if ($or)
		$args[] = $or;
	$customers = (array) call_user_func_array(array($pines->entity_manager, 'get_entities'), $args);
	$count_customers = count($customers);
	// Only bother searching companies if the limit hasn't been reached.
	if ($pines->config->com_customer->customer_search_limit - $count_customers) {
		$companies = $pines->entity_manager->get_entities(
				array('class' => com_customer_company),
				array('&',
					'tag' => array('com_customer', 'company'),
					'match' => array('name', $r_query)
				)
			);
		if ($companies) {
			$comp_customers = (array) $pines->entity_manager->get_entities(
					array('class' => com_customer_customer, 'limit' => ($pines->config->com_customer->customer_search_limit - $count_customers)),
					array('&',
						'tag' => array('com_customer', 'customer'),
						'ref' => array('company', $companies)
					)
				);
			foreach ($comp_customers as &$cur_customer) {
				if (!$cur_customer->in_array($customers))
					$customers[] = $cur_customer;
			}
		}
	}
}

foreach ($customers as $key => &$cur_customer) {
	$json_struct = (object) array(
		'guid'			=> (int) $cur_customer->guid,
		'username'		=> (string) $cur_customer->username,
		'name'			=> (string) $cur_customer->name,
		'location'		=> (string) $cur_customer->group->name,
		'email'			=> (string) $cur_customer->email,
		'company'		=> $cur_customer->company->name ? $cur_customer->company->name : '',
		'title'			=> (string) $cur_customer->job_title,
		'address_1'		=> (string) $cur_customer->address_1,
		'address_2'		=> (string) $cur_customer->address_2,
		'city'			=> (string) $cur_customer->city,
		'state'			=> (string) $cur_customer->state,
		'zip'			=> (string) $cur_customer->zip,
		'phone_home'	=> format_phone($cur_customer->phone_home),
		'phone_work'	=> format_phone($cur_customer->phone_work),
		'phone_cell'	=> format_phone($cur_customer->phone_cell),
		'fax'			=> format_phone($cur_customer->fax),
		'cdate'			=> format_date($cur_customer->p_cdate),
		'enabled'		=> (bool) $cur_customer->has_tag('enabled'),
		'member'		=> (bool) $cur_customer->member,
		'valid_member'	=> (bool) $cur_customer->valid_member(),
		'member_exp'	=> $cur_customer->member_exp ? format_date($cur_customer->member_exp) : '',
		'points'		=> (int) $cur_customer->points,
		'referral'		=> $cur_customer->referral_code
	);
	$cur_customer = $json_struct;
}
unset($cur_customer);

if (!$customers)
	$customers = null;

$pines->page->override_doc(json_encode($customers));

?>