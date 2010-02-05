<?php
/**
 * Search customers, returning JSON.
 *
 * @package Pines
 * @subpackage com_customer
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Zak Huber <zakhuber@gmail.com>
 * @copyright Zak Huber
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_customer/listcompanies') )
	punt_user('You don\'t have necessary permission.', pines_url('com_customer', 'companysearch', $_REQUEST, false));

$config->page->override = true;

$query = strtolower($_REQUEST['q']);

if (empty($query)) {
	$companies = array();
} else {
	// TODO: Use 'match_i' instead.
	$companies = $config->entity_manager->get_entities(array('tags' => array('com_customer', 'company'), 'class' => com_customer_company));
	if (!is_array($companies))
		$companies = array();
}

foreach ($companies as $key => &$cur_company) {
	if (
		(strpos(strtolower($cur_company->name), $query) !== false) ||
		(strpos(strtolower($cur_company->address_type), $query) !== false) ||
		(strpos(strtolower($cur_company->city), $query) !== false) ||
		(strpos(strtolower($cur_company->state), $query) !== false) ||
		(strpos(strtolower($cur_company->zip), $query) !== false) ||
		(strpos(strtolower($cur_company->email), $query) !== false) ||
		(preg_replace('/\D/', '', $query) != '' && strpos($cur_company->phone, preg_replace('/\D/', '', $query)) !== false) ||
		(preg_replace('/\D/', '', $query) != '' && strpos($cur_company->fax, preg_replace('/\D/', '', $query)) !== false) ||
		(strpos(strtolower("{$cur_company->website}"), $query) !== false)
		) {
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
	} else {
		unset($companies[$key]);
	}
}

if (empty($companies))
	$companies = null;

$config->page->override_doc(json_encode($companies));

?>