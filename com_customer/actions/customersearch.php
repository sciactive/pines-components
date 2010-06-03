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
	$selector = array('|',
		'match' => array(
			array('name', $r_query),
			array('email', $r_query)
		)
	);
	if ($num_query != '') {
		$selector['match'][] = array('phone_home', $r_num_query);
		$selector['match'][] = array('phone_work', $r_num_query);
		$selector['match'][] = array('phone_cell', $r_num_query);
		$selector['match'][] = array('fax', $r_num_query);
	}
	$customers = (array) $pines->entity_manager->get_entities(
			array('class' => com_customer_customer),
			array('&', 'tag' => array('com_customer', 'customer')),
			$selector
		);
	$companies = $pines->entity_manager->get_entities(
			array('class' => com_customer_company),
			array('&',
				'match' => array('name', $r_query),
				'tag' => array('com_customer', 'company')
			)
		);
	if ($companies) {
		$comp_customers = (array) $pines->entity_manager->get_entities(
				array('class' => com_customer_customer),
				array('&',
					'ref' => array('company', $companies),
					'tag' => array('com_customer', 'customer')
				)
			);
		foreach ($comp_customers as &$cur_customer) {
			if (!$cur_customer->in_array($customers))
				$customers[] = $cur_customer;
		}
	}
}

foreach ($customers as $key => &$cur_customer) {
	$json_struct = (object) array(
		'guid'			=> $cur_customer->guid,
		'name'			=> $cur_customer->name,
		'email'			=> $cur_customer->email,
		'company'		=> $cur_customer->company->name ? $cur_customer->company->name : '',
		'title'			=>	$cur_customer->job_title,
		'address_1'		=> $cur_customer->address_1,
		'address_2'		=>	$cur_customer->address_2,
		'city'			=>	$cur_customer->city,
		'state'			=>	$cur_customer->state,
		'zip'			=>	$cur_customer->zip,
		'phone_home'	=>	format_phone($cur_customer->phone_home),
		'phone_work'	=>	format_phone($cur_customer->phone_work),
		'phone_cell'	=>	format_phone($cur_customer->phone_cell),
		'fax'			=>	format_phone($cur_customer->fax),
		'login_disabled'=>	$cur_customer->login_disabled,
		'member'		=>	$cur_customer->member,
		'valid_member'	=>  $cur_customer->valid_member(),
		'member_exp'	=>	format_date($cur_customer->member_exp),
		'points'		=>	$cur_customer->points
	);
	$cur_customer = $json_struct;
}

if (!$customers)
	$customers = null;

$pines->page->override_doc(json_encode($customers));

?>