<?php
/**
 * Fix category hierarchy in products.
 *
 * @package Components\sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/editcategory') || !gatekeeper('com_sales/editproduct') )
	punt_user(null, pines_url('com_sales', 'category/fixhier'));

foreach ($_REQUEST['fixes'] as $cur_key) {
	// Get product and category.
	list ($product_id, $cat_id) = explode('_', $cur_key, 2);
	$cur_product = com_sales_product::factory((int) $product_id);
	if (!isset($cur_product->guid)) {
		pines_notice("Couldn't find product with id $product_id.");
		continue;
	}
	$cur_cat = com_sales_category::factory((int) $cat_id);
	if (!isset($cur_cat->guid)) {
		pines_notice("Couldn't find category with id $cat_id.");
		continue;
	}
	// Remove the product from the category.
	if ($cur_product->in_array($cur_cat->products)) {
		$key = $cur_product->array_search($cur_cat->products);
		unset($cur_cat->products[$key]);
		if (!$cur_cat->save())
			pines_error("Couldn't remove product {$cur_product->name} from category {$cur_cat->name}. Do you have permission?");
	}
}

pines_notice('Finished making changes.');
pines_redirect(pines_url('com_sales', 'category/list'));

?>