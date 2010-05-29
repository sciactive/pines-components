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
	$selector = array('|',
		'match' => array(
			array('name', $r_query),
			array('email', $r_query),
			array('address_international', $r_query),
			array('city', $r_query),
			array('state', $r_query),
			array('website', $r_query)
		)
	);
	if ($num_query != '') {
		$selector['match'][] = array('phone', $r_num_query);
		$selector['match'][] = array('zip', $r_num_query);
		$selector['match'][] = array('fax', $r_num_query);
	}
	$companies = (array) $pines->entity_manager->get_entities(
			array('class' => com_customer_company),
			array('&',
				'tag' => array('com_customer', 'company')
			),
			$selector
		);
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
			format_phone($cur_company->phone),
			format_phone($cur_company->fax),
			$cur_company->website
		)
	);
	$cur_company = $json_struct;
}

if (empty($companies))
	$companies = null;

$pines->page->override_doc(json_encode($companies));

?>