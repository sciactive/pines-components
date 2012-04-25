<?php
/**
 * Search products, returning JSON.
 *
 * @package Components
 * @subpackage sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/searchproducts') )
	punt_user(null, pines_url('com_sales', 'product/autocomplete', $_REQUEST));

$pines->page->override = true;
header('Content-Type: application/json');

$query = $_REQUEST['q'];

if (empty($query)) {
	$products = array();
} else {
	$selector = array('&',
			'tag' => array('com_sales', 'product'),
			'strict' => array(
				array('enabled', true)
			)
		);
	$notselector = array('!&',
			'strict' => array(
				array('autocomplete_hide', true)
			)
		);
	$or = array('|');
	$query = explode(' ', $query);
	foreach ($query as $key => $subquery) {
		if (strpos($subquery, ':') === false)
			continue;
		list($option, $value) = explode(':', $subquery, 2);
		switch ($option) {
			case 'enabled':
				$selector['strict'][0][1] = ($value == 'true');
				break;
			case 'serialized':
				$selector['strict'][] = array('serialized', ($value == 'true'));
				break;
			case 'storefront':
				$selector['strict'][] = array('show_in_storefront', ($value == 'true'));
				break;
			case 'featured':
				$selector['strict'][] = array('featured', ($value == 'true'));
				break;
			default:
				continue 2;
		}
		unset($query[$key]);
	}
	if ($query) {
		$r_query = '/'.preg_quote(implode(' ', $query)).'/i';
		if (!$or['match'])
			$or['match'] = array();
		$or['match'][] = array('name', $r_query);
		$or['match'][] = array('sku', $r_query);
	}
	if ($or != array('|'))
		$products = (array) $pines->entity_manager->get_entities(array('class' => com_sales_product), $selector, $notselector, $or);
	else
		$products = (array) $pines->entity_manager->get_entities(array('class' => com_sales_product), $selector, $notselector);
}

foreach ($products as $key => &$cur_product) {
	$json_struct = (object) array(
		'guid'					=> $cur_product->guid,
		'name'					=> $cur_product->name,
		'sku'					=> $cur_product->sku,
		'receipt_description'	=> $cur_product->receipt_description,
		'unit_price'			=> $cur_product->unit_price
	);
	$cur_product = $json_struct;
}

if (!$products)
	$products = null;

$pines->page->override_doc(json_encode($products));

?>