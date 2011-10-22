<?php
/**
 * Search sales, returning JSON.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/listsales') )
	punt_user(null, pines_url('com_sales', 'sales/search', $_REQUEST));

$pines->page->override = true;
header('Content-Type: application/json');

$sales = array();
// This array will be customized, and used to search for sales entities.
$sales_query = array('&',
	'tag' => array('com_sales', 'sale')
);
// Add a status clause to select upon.
if (isset($_REQUEST['status']))
	$sales_query['data'] = array('status', $_REQUEST['status']);
if ($pines->config->com_sales->com_customer) {
	if (isset($_REQUEST['customer'])) {
		// Looking for sales made to a specific customer.
		$customer = com_customer_customer::factory((int) $_REQUEST['customer']);
		$sales_query['ref'] = array('customer', $customer);
		$sales = (array) $pines->entity_manager->get_entities(array('class' => com_sales_sale), $sales_query);
	} else {
		// Looking for sales made to any customers matching the query.
		$query = strtolower($_REQUEST['q']);
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
			$selector['data'][] = array('guid', $num_query);
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
		foreach ($customers as $cur_customer) {
			$sales_query['ref'] = array('customer', $cur_customer);
			$sales = array_merge($sales, $pines->entity_manager->get_entities(array('class' => com_sales_sale), $sales_query));
		}
	}
} else {
	$sales = (array) $pines->entity_manager->get_entities(array('class' => com_sales_sale), $sales_query);
}

foreach ($sales as $key => &$cur_sale) {
	$product_names = array();
	foreach ($cur_sale->products as $cur_product) {
		$product_names[] = $cur_product['entity']->name;
	}
	$json_struct = (object) array(
		'guid'			=> $cur_sale->guid,
		'id'			=> $cur_sale->id,
		'status'		=> $cur_sale->status,
		'total'			=> '$'.$cur_sale->total,
		'cdate'			=> format_date($cur_sale->p_cdate),
		'mdate'			=> format_date($cur_sale->p_mdate),
		'invoice_date'	=> ($cur_sale->invoice_date ? format_date($cur_sale->invoice_date) : ''),
		'tender_date'	=> ($cur_sale->tender_date ? format_date($cur_sale->tender_date) : ''),
		'void_date'		=> ($cur_sale->void_date ? format_date($cur_sale->void_date) : ''),
		'products'		=> implode(', ', $product_names),
		'location'		=> $cur_sale->group->name
	);
	if ($pines->config->com_sales->com_customer) {
		$json_struct->customer = $cur_sale->customer->guid;
		$json_struct->customer_name = $cur_sale->customer->name;
	}
	$cur_sale = $json_struct;
}
unset($cur_sale);

if (empty($sales))
	$sales = null;

$pines->page->override_doc(json_encode($sales));

?>