<?php
/**
 * Save changes to a product.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( isset($_REQUEST['id']) ) {
	if ( !gatekeeper('com_sales/editproduct') )
		punt_user('You don\'t have necessary permission.', pines_url('com_sales', 'product/list'));
	$product = com_sales_product::factory((int) $_REQUEST['id']);
	if (!isset($product->guid)) {
		pines_error('Requested product id is not accessible.');
		return;
	}
} else {
	if ( !gatekeeper('com_sales/newproduct') )
		punt_user('You don\'t have necessary permission.', pines_url('com_sales', 'product/list'));
	$product = com_sales_product::factory();
}

// General
$product->name = $_REQUEST['name'];
$product->enabled = ($_REQUEST['enabled'] == 'ON');
$product->autocomplete_hide = ($_REQUEST['autocomplete_hide'] == 'ON');
$product->sku = $_REQUEST['sku'];
$product->description = $_REQUEST['description'];
$product->short_description = $_REQUEST['short_description'];
$product->manufacturer = ($_REQUEST['manufacturer'] == 'null' ? null : com_sales_manufacturer::factory((int) $_REQUEST['manufacturer']));
if (!isset($product->manufacturer->guid))
	$product->manufacturer = null;
$product->manufacturer_sku = $_REQUEST['manufacturer_sku'];

// Images
$product->images = (array) json_decode($_REQUEST['images']);
foreach ($product->images as $key => $cur_image) {
	if (!$pines->uploader->check($cur_image))
		unset($product->images[$key]);
}
$product->images = array_values($product->images);
$product->thumbnail = $_REQUEST['thumbnail'];
if (!$pines->uploader->check($product->thumbnail))
	$product->thumbnail = null;

// Purchasing
$product->stock_type = $_REQUEST['stock_type'];
$product->vendors = (array) json_decode($_REQUEST['vendors']);
foreach ($product->vendors as &$cur_vendor) {
	$cur_vendor = array(
		'entity' => com_sales_vendor::factory((int) $cur_vendor->key),
		'sku' => $cur_vendor->values[1],
		'cost' => $cur_vendor->values[2]
	);
	if (!isset($cur_vendor['entity']->guid))
		$cur_vendor['entity'] = null;
}
unset($cur_vendor);

// Pricing
$product->pricing_method = $_REQUEST['pricing_method'];
$product->unit_price = (float) $_REQUEST['unit_price'];
$product->margin = (float) $_REQUEST['margin'];
$product->floor = (float) $_REQUEST['floor'];
$product->ceiling = (float) $_REQUEST['ceiling'];
// TODO: Tax exempt by location.
$product->tax_exempt = ($_REQUEST['tax_exempt'] == 'ON');
$product->additional_tax_fees = array();
if (is_array($_REQUEST['additional_tax_fees'])) {
	foreach ($_REQUEST['additional_tax_fees'] as $cur_tax_fee_guid) {
		$cur_tax_fee = com_sales_tax_fee::factory((int) $cur_tax_fee_guid);
		if (isset($cur_tax_fee->guid))
			$product->additional_tax_fees[] = $cur_tax_fee;
	}
}

// Attributes
$product->weight = (float) $_REQUEST['weight'];
$product->rma_after = (float) $_REQUEST['rma_after'];
$product->serialized = ($_REQUEST['serialized'] == 'ON');
$product->discountable = ($_REQUEST['discountable'] == 'ON');
$product->require_customer = ($_REQUEST['require_customer'] == 'ON');
$product->one_per_ticket = ($_REQUEST['one_per_ticket'] == 'ON');
$product->hide_on_invoice = ($_REQUEST['hide_on_invoice'] == 'ON');
$product->non_refundable = ($_REQUEST['non_refundable'] == 'ON');
$product->additional_barcodes = explode(',', $_REQUEST['additional_barcodes']);
$product->actions = (array) $_REQUEST['actions'];

// Commission
if ($pines->config->com_sales->com_hrm) {
	$product->commissions = (array) json_decode($_REQUEST['commissions']);
	foreach ($product->commissions as $key => &$cur_commission) {
		$cur_commission = array(
			'group' => group::factory((int) $cur_commission->values[0]),
			'type' => $cur_commission->values[1],
			'amount' => (float) $cur_commission->values[2]
		);
		if (!isset($cur_commission['group']->guid) || !in_array($cur_commission['type'], array('spiff', 'percent_price')))
			unset($product->commissions[$key]);
	}
	unset($cur_commission);
}

// Storefront
if ($pines->config->com_sales->com_storefront) {
	$product->show_in_storefront = ($_REQUEST['show_in_storefront'] == 'ON');
	$product->featured = ($_REQUEST['featured'] == 'ON');
	$product->featured_image = $_REQUEST['featured_image'];
	if (!$pines->uploader->check($product->featured_image))
		$product->featured_image = null;
	// Build a list of categories.
	$categories = array();
	if (is_array($_REQUEST['categories']))
		$categories = array_map('intval', $_REQUEST['categories']);
	$categories = (array) $pines->entity_manager->get_entities(
			array('class' => com_sales_category),
			array('|',
				'guid' => $categories
			),
			array('&',
				'data' => array('enabled', true),
				'tag' => array('com_sales', 'category')
			)
		);
	// Build a list of specs.
	$specs = array();
	foreach ($categories as &$cur_category) {
		$specs = array_merge($specs, $cur_category->get_specs_all());
	}
	unset($categories, $cur_category);
	// Save specs.
	$product->specs = array();
	foreach ($specs as $key => $cur_spec) {
		switch ($cur_spec['type']) {
			case 'bool':
				$product->specs[$key] = ($_REQUEST[$key] == 'ON');
				break;
			case 'string':
				$product->specs[$key] = (string) $_REQUEST[$key];
				if ($cur_spec['restricted'] && !in_array($product->specs[$key], $cur_spec['options']))
					unset($product->specs[$key]);
				break;
			case 'float':
				$product->specs[$key] = (float) $_REQUEST[$key];
				if ($cur_spec['restricted'] && !in_array($product->specs[$key], $cur_spec['options']))
					unset($product->specs[$key]);
				break;
			default:
				break;
		}
	}
	unset($specs);
}

if (empty($product->name)) {
	$product->print_form();
	pines_notice('Please specify a name.');
	return;
}
if ($product->stock_type == 'non_stocked' && $product->pricing_method == 'margin') {
	$product->print_form();
	pines_notice('Margin pricing is not available for non stocked items.');
	return;
}
$test = $pines->entity_manager->get_entity(array('class' => com_sales_product, 'skip_ac' => true), array('&', 'data' => array('name', $product->name), 'tag' => array('com_sales', 'product')));
if (isset($test) && $test->guid != $_REQUEST['id']) {
	$product->print_form();
	pines_notice('There is already a product with that name. Please choose a different name.');
	return;
}

if ($pines->config->com_sales->global_products)
	$product->ac->other = 1;

if ($product->save()) {
	pines_notice('Saved product ['.$product->name.']');
	// Assign the product to the selected categories.
	// We have to do this here, because new products won't have a GUID until now.
	$categories = array();
	if (is_array($_REQUEST['categories']))
		$categories = array_map('intval', $_REQUEST['categories']);
	$all_categories = $pines->entity_manager->get_entities(array('class' => com_sales_category), array('&', 'data' => array('enabled', true), 'tag' => array('com_sales', 'category')));
	foreach($all_categories as &$cur_cat) {
		if (in_array($cur_cat->guid, $categories) && !$product->in_array($cur_cat->products)) {
			$cur_cat->products[] = $product;
			if (!$cur_cat->save())
				pines_error("Couldn't add product to category {$cur_cat->name}. Do you have permission?");
		} elseif (!in_array($cur_cat->guid, $categories) && $product->in_array($cur_cat->products)) {
			$key = $product->array_search($cur_cat->products);
			unset($cur_cat->products[$key]);
			if (!$cur_cat->save())
				pines_error("Couldn't remove product from category {$cur_cat->name}. Do you have permission?");
		}
	}
	unset($cur_cat);
} else {
	pines_error('Error saving product. Do you have permission?');
}

redirect(pines_url('com_sales', 'product/list'));

?>