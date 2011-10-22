<?php
/**
 * com_storefront's configuration.
 *
 * @package Pines
 * @subpackage com_storefront
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	array(
		'name' => 'catalog_mode',
		'cname' => 'Catalog Mode',
		'description' => 'Only show products. Don\'t provide a cart and make sales.',
		'value' => false,
		'peruser' => true,
	),
	array(
		'name' => 'show_categories',
		'cname' => 'Show Category Menus',
		'description' => 'Show category menus from com_sales.',
		'value' => true,
		'peruser' => true,
	),
	array(
		'name' => 'products_per_page',
		'cname' => 'Products Per Page',
		'description' => 'Number of products to show per page in categories.',
		'value' => 9,
		'peruser' => true,
	),
	array(
		'name' => 'products_from_children',
		'cname' => 'Show Child Products',
		'description' => 'Show products from child categories.',
		'value' => true,
		'peruser' => true,
	),
	array(
		'name' => 'category_template',
		'cname' => 'Category Page Template',
		'description' => 'Alters the layout of products on the category pages.',
		'value' => 'rows',
		'options' => array(
			'rows',
			'grid'
		),
		'peruser' => true,
	),
	array(
		'name' => 'image_thumbnails',
		'cname' => 'Product Image Thumbnails',
		'description' => 'Show product image thumbnails. (240px x 200px max)',
		'value' => false,
		'peruser' => true,
	),
	array(
		'name' => 'image_thumbnails_suffix',
		'cname' => 'Product Image Thumbnails Suffix',
		'description' => 'The suffix to append to image paths to find the thumbnail.',
		'value' => '_t',
		'peruser' => true,
	),
	array(
		'name' => 'cart_prices',
		'cname' => 'Show Prices in Cart Module',
		'description' => 'Show prices for items in the cart module.',
		'value' => true,
		'peruser' => true,
	),
	array(
		'name' => 'cart_subtotal',
		'cname' => 'Show Subtotal in Cart Module',
		'description' => 'Show subtotal of items in the cart module.',
		'value' => true,
		'peruser' => true,
	),
	array(
		'name' => 'cart_link',
		'cname' => 'Link to Cart in Cart Module',
		'description' => 'Show link to the bigger cart view in the cart module.',
		'value' => false,
		'peruser' => true,
	),
	array(
		'name' => 'skip_shipping',
		'cname' => 'Skip Shipping Page',
		'description' => 'Skip the shipping page and go straight to the review. The user can then click "Change" if they need to change their shipping address. This will only have an effect if the user has an address on file.',
		'value' => false,
		'peruser' => true,
	),
	array(
		'name' => 'review_in_payment_page',
		'cname' => 'Combine Review and Payment Step',
		'description' => 'Combine the payment options page and review page into one step.',
		'value' => false,
		'peruser' => true,
	),
	array(
		'name' => 'complete_order_text',
		'cname' => 'Complete Order Text',
		'description' => 'The text in the button used to complete the order.',
		'value' => 'Complete My Order',
		'peruser' => true,
	),
);

?>