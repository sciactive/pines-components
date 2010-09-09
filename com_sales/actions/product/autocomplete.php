<?php
/**
 * Search products, returning JSON.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/searchproducts') )
	punt_user(null, pines_url('com_sales', 'product/autocomplete', $_REQUEST));

$pines->page->override = true;

$query = $_REQUEST['q'];

if (empty($query)) {
	$products = array();
} else {
	$num_query = preg_replace('/\D/', '', $query);
	$r_query = '/'.preg_quote($query).'/i';
	$r_num_query = '/'.preg_quote($num_query).'/';
	$selector = array('|',
		'match' => array(
			array('name', $r_query),
			array('sku', $r_query)
		)
	);
	if ($num_query != '') {
		$selector['match'][] = array('name', $r_num_query);
		$selector['match'][] = array('sku', $r_num_query);
	}
	$products = (array) $pines->entity_manager->get_entities(
			array('class' => com_sales_product),
			array('&', 'tag' => array('com_sales', 'product')),
			array('!&', 'data' => array('autocomplete_hide', true)),
			$selector
		);
}

foreach ($products as $key => &$cur_product) {
	$json_struct = (object) array(
		'guid'				=> $cur_product->guid,
		'name'				=> $cur_product->name,
		'sku'				=> $cur_product->sku,
		'short_description'	=> $cur_product->short_description,
		'unit_price'		=> $cur_product->unit_price
	);
	$cur_product = $json_struct;
}

if (!$products)
	$products = null;

$pines->page->override_doc(json_encode($products));

?>