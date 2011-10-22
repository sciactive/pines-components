<?php
/**
 * com_storefront class.
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

/**
 * com_storefront main class.
 *
 * @package Pines
 * @subpackage com_storefront
 */
class com_storefront extends component {
	/**
	 * The shopping cart, using entities instead of GUIDs.
	 * @var array
	 */
	private $cart = array();
	/**
	 * Holds the state of the cookie the last time the cart was synchronized.
	 * @var array
	 */
	private $cart_cookie = array();
	/**
	 * Printed the checkout step image yet?
	 * @var bool
	 */
	private $printed_checkout_step = false;

	/**
	 * Add a product to the cart.
	 *
	 * @param int $product_guid The product's GUID.
	 * @return bool|string True on success, false on failure, "one_per" if only one of the product is allowed per ticket.
	 */
	public function add_to_cart($product_guid) {
		$product = com_sales_product::factory((int) $product_guid);
		if (!isset($product->guid))
			return false;
		$this->cart();
		$added = false;
		foreach ($this->cart as &$value) {
			if ($product->is($value['product'])) {
				if ($product->one_per_ticket)
					return 'one_per';
				$value['quantity']++;
				$added = true;
				break;
			}
		}
		unset($value);
		if (!$added)
			$this->cart[] = array('product' => $product, 'quantity' => 1);
		$this->save_cart();
		return true;
	}

	/**
	 * Adjust a product's quantity in the cart.
	 *
	 * @param int $product_guid The product's GUID.
	 * @param int $new_qty The product's new quantity.
	 * @return bool|string True on success, false on failure, "one_per" if only one of the product is allowed per ticket.
	 */
	public function adjust_quantity($product_guid, $new_qty) {
		if ((int) $new_qty < 1)
			return false;
		$product = com_sales_product::factory((int) $product_guid);
		if (!isset($product->guid))
			return false;
		$this->cart();
		foreach ($this->cart as &$value) {
			if ($product->is($value['product'])) {
				if ($new_qty > 1 && $product->one_per_ticket)
					return 'one_per';
				$value['quantity'] = (int) $new_qty;
				break;
			}
		}
		unset($value);
		$this->save_cart();
		return true;
	}

	/**
	 * Build a sale from the cart products.
	 *
	 * @return bool Whether the sale could be set up.
	 * @todo Verify products. (One per ticket...)
	 */
	public function build_sale() {
		if (!$_SESSION['user']->has_tag('com_customer', 'customer')) {
			pines_notice('You are not logged in as a customer.');
			return false;
		}
		$sale = $_SESSION['com_storefront_sale'];
		if (!isset($sale)) {
			global $pines;
			$sale = com_sales_sale::factory();
			$sale->add_tag('com_storefront');
			$sale->status = 'quoted';
			$sale->customer = $_SESSION['user'];
			if ($pines->config->com_sales->global_sales)
				$sale->ac->other = 1;
		}

		if ($sale->status == 'paid' || $sale->status == 'voided') {
			pines_notice('This sale is already processed.');
			return true;
		}

		if ($sale->status == 'quoted') {
			// Products.
			$cart = $this->cart();
			$sale->warehouse = true;
			$sale->products = array();
			foreach ($cart as $cur_product) {
				$sale->products[] = array(
					'entity' => $cur_product['product'],
					'sku' => $cur_product['product']->sku,
					'serial' => '',
					'delivery' => 'warehouse',
					'quantity' => $cur_product['quantity'],
					'price' => $cur_product['product']->unit_price,
					'discount' => '',
					'stock_entities' => array()
				);
			}

			if (!$sale->products) {
				pines_error('Couldn\'t load cart contents.');
				return false;
			}

			// Total it.
			$sale->total();
		}
		pines_session('write');
		$_SESSION['com_storefront_sale'] = $sale;
		pines_session('close');
		return true;
	}

	/**
	 * Retrieve cart.
	 *
	 * @return array The cart.
	 */
	public function cart() {
		if ($this->cart_cookie == $_COOKIE['com_storefront_cart'])
			return $this->cart;
		$this->cart = array();
		$cart = (array) json_decode($_COOKIE['com_storefront_cart'], true);
		foreach ($cart as $cur_item) {
			if ((array) $cur_item !== $cur_item)
				continue;
			$item = array(
				'product' => com_sales_product::factory((int) $cur_item['product']),
				'quantity' => (int) $cur_item['quantity']
			);
			if (!isset($item['product']->guid))
				continue;
			$this->cart[] = $item;
		}
		$this->cart_cookie = $_COOKIE['com_storefront_cart'];
		return $this->cart;
	}

	/**
	 * Print the checkout steps image.
	 *
	 * @param int $step The current step.
	 */
	public function checkout_step($step) {
		if ($this->printed_checkout_step || !isset($step))
			return;
		$this->printed_checkout_step = true;
		$module = new module('com_storefront', 'checkout/step', 'content');
		$module->step = $step;
	}

	/**
	 * Empty the cart.
	 * 
	 * @return bool True.
	 */
	public function empty_cart() {
		$this->cart = array();
		$this->save_cart();
		$_COOKIE['com_storefront_cart'] = '[]';
		return true;
	}

	/**
	 * Format a price to display to the user.
	 *
	 * @param float $price The original price.
	 * @param bool $short Whether to show a short price.
	 * @return string The formatted price.
	 */
	public function format_price($price, $short = false) {
		return '$'.number_format((float) $price, 2);
	}

	/**
	 * Get the products to display from a category.
	 *
	 * @param com_sales_category $category The category.
	 * @param int $page The page number.
	 * @param int $products_per_page The number of products per page.
	 * @param mixed &$offset This variable will receive the product offset.
	 * @param mixed &$count This variable will receive the total number of products.
	 * @param mixed &$pages This variable will receive the total number of pages.
	 * @param string|null $sort_var The variable by which the products should be sorted. If null, no sorting.
	 * @return array The array of products.
	 */
	public function get_cat_products($category, $page, $products_per_page, &$offset, &$count, &$pages, $sort_var = null, $sort_reverse = false) {
		global $pines;
		if ($pines->config->com_storefront->products_from_children)
			$products = $this->get_child_products($category);
		else
			$products = (array) $category->products;

		// Get the products to be displayed.
		foreach ($products as $key => $cur_product) {
			if (!isset($cur_product->guid) || !$cur_product->enabled || !$cur_product->show_in_storefront)
				unset($products[$key]);
		}

		if (isset($sort_var))
			$pines->entity_manager->sort($products, $sort_var, false, $sort_reverse);

		// How many products/pages are there?
		$count = count($products);
		$pages = ceil($count / $products_per_page);

		// What's the first product to show?
		$offset = ($page -1) * $products_per_page;

		// Get the products to show;
		$products = array_slice($products, $offset, $products_per_page);

		return $products;
	}

	/**
	 * Get all products in a category and its descendents.
	 *
	 * @param com_sales_category $category The category.
	 * @return array The array of products.
	 */
	public function get_child_products($category) {
		$products = (array) $category->products;
		if (empty($category->children))
			return $products;
		foreach ($category->children as $cur_child) {
			$child_products = $this->get_child_products($cur_child);
			foreach ($child_products as $cur_product) {
				if (!isset($cur_product) || !$cur_product->enabled || !$cur_product->show_in_storefront)
					continue;
				if (!$cur_product->in_array($products))
					$products[] = $cur_product;
			}
		}
		return $products;
	}

	/**
	 * Remove a product from the cart.
	 *
	 * @param int $product_guid The product's GUID.
	 * @return bool True on success, false on failure.
	 */
	public function remove_from_cart($product_guid) {
		$product = com_sales_product::factory((int) $product_guid);
		if (!isset($product->guid))
			return false;
		$this->cart();
		foreach ($this->cart as $key => &$value) {
			if ($product->is($value['product']))
				unset($this->cart[$key]);
		}
		unset($value);
		$this->save_cart();
		return true;
	}

	/**
	 * Save the cart in the cookie.
	 */
	public function save_cart() {
		global $pines;
		$cookie = array();
		foreach ($this->cart as $cur_item) {
			$cookie[] = array(
				'product' => (int) $cur_item['product']->guid,
				'quantity' => (int) $cur_item['quantity']
			);
		}
		$json = json_encode($cookie);
		$this->cart_cookie = $json;
		setcookie('com_storefront_cart', $json, 0, $pines->config->rela_location);
	}

	/**
	 * List all storefront products that are problematic/incomplete.
	 */
	public function verify_stock() {
		global $pines;

		$module = new module('com_storefront', 'verify_stock', 'content');
		$module->images = $module->image_descs = array();

		$entities = $pines->entity_manager->get_entities(
					array('limit' => 50, 'offset' => $offset, 'class' => com_sales_product),
					array('&',
						'tag' => array('com_sales', 'product'),
						'data' => array(
							array('enabled', true),
							array('show_in_storefront', true)
						)
					)
				);

		foreach ($entities as $key => $cur_entity) {
			// Check the stock type.
			if ($cur_entity->stock_type != 'stock_optional')
				$module->items[] = $cur_entity;
			// Check the image descriptions.
			foreach ($cur_entity->images as &$cur_image) {
				if ($cur_image['alt'] == '') {
					$module->image_descs[] = $cur_entity;
					break;
				}
			}
		}
	}
}

?>