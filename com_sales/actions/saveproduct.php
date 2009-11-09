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
	$product = $config->run_sales->get_product($_REQUEST['id']);
    if (is_null($product)) {
        display_error('Requested product id is not accessible');
        return;
    }
} else {
	if ( !gatekeeper('com_sales/newproduct') ) {
		$config->user_manager->punt_user("You don't have necessary permission.", pines_url('com_sales', 'listproducts', null, false));
		return;
	}
	$product = new entity;
    $product->add_tag('com_sales', 'product');
}

$product->name = $_REQUEST['name'];
$product->enabled = ($_REQUEST['enabled'] == 'ON' ? true : false);
$product->sku = $_REQUEST['sku'];
$product->description = $_REQUEST['description'];
$product->short_description = $_REQUEST['short_description'];
$product->manufacturer = ($_REQUEST['manufacturer'] == 'null' ? null : intval($_REQUEST['manufacturer']));
$product->manufacturer_sku = $_REQUEST['manufacturer_sku'];
$product->average_cost = floatval($_REQUEST['average_cost']);
$product->pricing_method = $_REQUEST['pricing_method'];
$product->unit_price = floatval($_REQUEST['unit_price']);
$product->margin = floatval($_REQUEST['margin']);
$product->floor = floatval($_REQUEST['floor']);
$product->weight = floatval($_REQUEST['weight']);
$product->rma_after = floatval($_REQUEST['rma_after']);
$product->discountable = ($_REQUEST['discountable'] == 'ON' ? true : false);
$product->hide_on_invoice = ($_REQUEST['hide_on_invoice'] == 'ON' ? true : false);
$product->non_refundable = ($_REQUEST['non_refundable'] == 'ON' ? true : false);
$product->additional_barcodes = explode(',', $_REQUEST['additional_barcodes']);
if (is_array($_REQUEST['additional_tax_fees'])) {
    $product->additional_tax_fees = array_map('intval', $_REQUEST['additional_tax_fees']);
} else {
    $product->additional_tax_fees = array();
}

if (empty($product->name)) {
    $module = $config->run_sales->print_product_form('Editing Product', 'com_sales', 'saveproduct');
    $module->entity = $product;
    display_error('Please specify a name.');
    return;
}
$test = $config->entity_manager->get_entities_by_data(array('name' => $product->name), array('com_sales', 'product'));
if (!empty($test) && $test[0]->guid != $_REQUEST['id']) {
    $module = $config->run_sales->print_product_form('Editing Product', 'com_sales', 'saveproduct');
    $module->entity = $product;
    display_error('There is already a product with that name. Please choose a different name.');
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