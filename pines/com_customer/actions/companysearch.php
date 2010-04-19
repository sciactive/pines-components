<?php
/**
 * Search companies, returning JSON.
 *
 * @package Pines
 * @subpackage com_customer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_customer/listcompanies') )
	punt_user('You don\'t have necessary permission.', pines_url('com_customer', 'companysearch', $_REQUEST));

$pines->page->override = true;

$query = strtolower($_REQUEST['q']);

if (empty($query)) {
	$companies = array();
} else {
	$num_query = preg_replace('/\D/', '', $query);
	$r_query = '/'.preg_quote($query).'/i';
	$r_num_query = '/'.preg_quote($num_query).'/';
	$params = array(
		'match_i' => array(
			'name' => $r_query,
			'email' => $r_query,
			'address_international' => $r_query,
			'city' => $r_query,
			'state' => $r_query,
			'website' => $r_query
		),
		'tags' => array('com_customer', 'company'),
		'class' => com_customer_company
	);
	if ($num_query != '') {
		$params['match_i']['phone'] = $r_num_query;
		$params['match_i']['zip'] = $r_num_query;
		$params['match_i']['fax'] = $r_num_query;
	}
	$companies = (array) $pines->entity_manager->get_entities($params);
}

foreach ($companies as $key => &$cur_company) {
	$json_struct = (object) array(
		'key' => $cur_company->guid,
		'values' => array(
			$cur_company->name,
			$cur_company->address_type == 'us' ? $cur_company->address_1 : $cur_company->address_international,
			$cur_company->city,
			$cur_company->state,
			$cur_company->zip,
			$cur_company->email,
			pines_phone_format($cur_company->phone),
			pines_phone_format($cur_company->fax),
			$cur_company->website
		)
	);
	$cur_company = $json_struct;
}

if (empty($companies))
	$companies = null;

$pines->page->override_doc(json_encode($companies));

?>