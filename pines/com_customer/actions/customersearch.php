<?php
/**
 * Search customers, returning JSON.
 *
 * @package Pines
 * @subpackage com_customer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_customer/listcustomers') )
	punt_user('You don\'t have necessary permission.', pines_url('com_customer', 'customersearch', $_REQUEST));

$pines->page->override = true;

$query = $_REQUEST['q'];

if (empty($query)) {
	$customers = array();
} else {
	$num_query = preg_replace('/\D/', '', $query);
	$r_query = '/'.preg_quote($query).'/i';
	$r_num_query = '/'.preg_quote($num_query).'/';
	$params = array(
		'match_i' => array(
			'name' => $r_query,
			'email' => $r_query
		),
		'tags' => array('com_customer', 'customer'),
		'class' => com_customer_customer
	);
	if ($num_query != '') {
		$params['match_i']['phone_home'] = $r_num_query;
		$params['match_i']['phone_work'] = $r_num_query;
		$params['match_i']['phone_cell'] = $r_num_query;
		$params['match_i']['fax'] = $r_num_query;
	}
	$customers = (array) $pines->entity_manager->get_entities($params);
	$companies = $pines->entity_manager->get_entities(array(
		'match' => array('name' => $r_query),
		'tags' => array('com_customer', 'company'),
		'class' => com_customer_company)
	);
	if ($companies) {
		$params = array(
			'ref' => array(
				'company' => $companies
			),
			'tags' => array('com_customer', 'customer'),
			'class' => com_customer_customer
		);
		$comp_customers = (array) $pines->entity_manager->get_entities($params);
		foreach ($comp_customers as &$cur_customer) {
			if (!$cur_customer->in_array($customers))
				$customers[] = $cur_customer;
		}
	}
}

foreach ($customers as $key => &$cur_customer) {
	$json_struct = (object) array(
		'key' => $cur_customer->guid,
		'values' => array(
			$cur_customer->name,
			$cur_customer->email,
			$cur_customer->company->name ? $cur_customer->company->name : '',
			$cur_customer->job_title,
			$cur_customer->address_1,
			$cur_customer->address_2,
			$cur_customer->city,
			$cur_customer->state,
			$cur_customer->zip,
			pines_phone_format($cur_customer->phone_home),
			pines_phone_format($cur_customer->phone_work),
			pines_phone_format($cur_customer->phone_cell),
			pines_phone_format($cur_customer->fax)
		)
	);
	$cur_customer = $json_struct;
}

if (!$customers)
	$customers = null;

$pines->page->override_doc(json_encode($customers));

?>