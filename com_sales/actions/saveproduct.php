<?php
/**
 * Save changes to a product.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( isset($_REQUEST['id']) ) {
	if ( !gatekeeper('com_sales/editproduct') ) {
		$config->user_manager->punt_user("You don't have necessary permission.", pines_url('com_sales', 'listproducts', null, false));
		return;
	}
	$product = com_sales_product::factory((int) $_REQUEST['id']);
	if (is_null($product->guid)) {
		display_error('Requested product id is not accessible');
		return;
	}
} else {
	if ( !gatekeeper('com_sales/newproduct') ) {
		$config->user_manager->punt_user("You don't have necessary permission.", pines_url('com_sales', 'listproducts', null, false));
		return;
	}
	$product = com_sales_product::factory();
}

// General
$product->name = $_REQUEST['name'];
$product->enabled = ($_REQUEST['enabled'] == 'ON' ? true : false);
$product->sku = $_REQUEST['sku'];
$product->description = $_REQUEST['description'];
$product->short_description = $_REQUEST['short_description'];
$product->manufacturer = ($_REQUEST['manufacturer'] == 'null' ? null : $config->run_sales->get_manufacturer(intval($_REQUEST['manufacturer'])));
$product->manufacturer_sku = $_REQUEST['manufacturer_sku'];

// Purchasing
$product->stock_type = $_REQUEST['stock_type'];
$product->vendors = json_decode($_REQUEST['vendors']);
if (!is_array($product->vendors))
	$product->vendors = array();
foreach ($product->vendors as &$cur_vendor) {
	$cur_vendor = array(
		'entity' => new com_sales_vendor(intval($cur_vendor->key)),
		'sku' => $cur_vendor->values[1],
		'cost' => $cur_vendor->values[2]
	);
	if (is_null($cur_vendor['entity']->guid))
		$cur_vendor['entity'] = null;
}
unset($cur_vendor);

// Pricing
if ($product->stock_type == 'non_stocked') {
	$product->pricing_method = 'fixed';
} else {
	$product->pricing_method = $_REQUEST['pricing_method'];
}
$product->unit_price = floatval($_REQUEST['unit_price']);
$product->margin = floatval($_REQUEST['margin']);
$product->floor = floatval($_REQUEST['floor']);
// TODO: Tax exempt by location.
$product->tax_exempt = ($_REQUEST['tax_exempt'] == 'ON' ? true : false);
$product->additional_tax_fees = array();
if (is_array($_REQUEST['additional_tax_fees'])) {
	foreach ($_REQUEST['additional_tax_fees'] as $cur_tax_fee_guid) {
		$cur_tax_fee = com_sales_tax_fee::factory(intval($cur_tax_fee_guid));
		if (isset($cur_tax_fee->guid))
			$product->additional_tax_fees[] = $cur_tax_fee;
	}
}

// Attributes
$product->weight = floatval($_REQUEST['weight']);
$product->rma_after = floatval($_REQUEST['rma_after']);
$product->serialized = ($_REQUEST['serialized'] == 'ON' ? true : false);
$product->discountable = ($_REQUEST['discountable'] == 'ON' ? true : false);
$product->require_customer = ($_REQUEST['require_customer'] == 'ON' ? true : false);
$product->hide_on_invoice = ($_REQUEST['hide_on_invoice'] == 'ON' ? true : false);
$product->non_refundable = ($_REQUEST['non_refundable'] == 'ON' ? true : false);
$product->additional_barcodes = explode(',', $_REQUEST['additional_barcodes']);
$product->actions = $_REQUEST['actions'];
if (!is_array($product->actions))
	$product->actions = array();

if (empty($product->name)) {
	$product->print_form();
	display_notice('Please specify a name.');
	return;
}
$test = $config->entity_manager->get_entities_by_data(array('name' => $product->name), array('com_sales', 'product'), false, com_sales_product);
if (!empty($test) && $test[0]->guid != $_REQUEST['id']) {
	$product->print_form();
	display_notice('There is already a product with that name. Please choose a different name.');
	return;
}

if ($config->com_sales->global_products) {
	$product->ac = (object) array('other' => 1);
}
if ($product->save()) {
	display_notice('Saved product ['.$product->name.']');
	// Assign the product to the selected categories.
	// We have to do this here, because new products won't have a GUID until now.
	$categories = json_decode($_REQUEST['categories']);
	if (is_array($categories)) {
		array_map('intval', $categories);
		$all_categories = $config->run_sales->get_category_array();
		foreach($all_categories as $cur_cat) {
			if (!is_array($cur_cat->products))
				$cur_cat->products = array();

			if (in_array($cur_cat->guid, $categories) && !in_array($product->guid, $cur_cat->products)) {
				$cur_cat->products[] = $product->guid;
				if (!$cur_cat->save())
					display_error('Failed to add product to category: '.$cur_cat->name);
			} elseif (!in_array($cur_cat->guid, $categories) && in_array($product->guid, $cur_cat->products)) {
				$cur_cat->products = array_diff($cur_cat->products, array($product->guid));
				if (!$cur_cat->save())
					display_error('Failed to remove product from category: '.$cur_cat->name);
			}
		}
	}
} else {
	display_error('Error saving product. Do you have permission?');
}

$config->run_sales->list_products();
?>