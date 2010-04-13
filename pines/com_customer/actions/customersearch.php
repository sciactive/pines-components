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

$query = strtolower($_REQUEST['q']);

if (empty($query)) {
	$customers = array();
} else {
	// TODO: Use 'match_i' instead.
	$customers = $pines->entity_manager->get_entities(array('tags' => array('com_customer', 'customer'), 'class' => com_customer_customer));
	if (!is_array($customers))
		$customers = array();
}

foreach ($customers as $key => &$cur_customer) {
	if (
		(strpos(strtolower($cur_customer->name), $query) !== false) ||
		(strpos(strtolower($cur_customer->email), $query) !== false) ||
		(strpos(strtolower("{$cur_customer->company->name}"), $query) !== false) ||
		(preg_replace('/\D/', '', $query) != '' && strpos($cur_customer->phone_home, preg_replace('/\D/', '', $query)) !== false) ||
		(preg_replace('/\D/', '', $query) != '' && strpos($cur_customer->phone_work, preg_replace('/\D/', '', $query)) !== false) ||
		(preg_replace('/\D/', '', $query) != '' && strpos($cur_customer->phone_cell, preg_replace('/\D/', '', $query)) !== false) ||
		(preg_replace('/\D/', '', $query) != '' && strpos($cur_customer->fax, preg_replace('/\D/', '', $query)) !== false)
		) {
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
	} else {
		unset($customers[$key]);
	}
}

if (empty($customers))
	$customers = null;

$pines->page->override_doc(json_encode($customers));

?>