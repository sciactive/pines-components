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

/**
 * Check categories for hierarchical errors.
 * @param type $cat The category to check.
 * @param type &$fixes The array to store the resulting fixes in.
 */
function com_sales__category_fix_check($cat, &$fixes) {
	// Get all descendants.
	$descendants = $cat->get_descendants();
	foreach ((array) $descendants as $cur_child) {
		if (!isset($cur_child->guid))
			continue;
		// For each of this cat's products, check if it's in a descendant.
		foreach ((array) $cat->products as $cur_product) {
			if (!isset($cur_product->guid))
				continue;
			if ($cur_product->in_array($cur_child->products)) {
				// Remember all the reasons it should be removed.
				if ($fixes[$cur_product->guid.'_'.$cat->guid])
					$fixes[$cur_product->guid.'_'.$cat->guid]['reasons'][] = $cur_child;
				else
					$fixes[$cur_product->guid.'_'.$cat->guid] = array(
						'product' => $cur_product,
						'category' => $cat,
						'reasons' => array($cur_child)
					);
			}
		}
	}
	// Now for this category's children, do the same.
	foreach ((array) $cat->children as $cur_child) {
		if (!isset($cur_child->guid))
			continue;
		com_sales__category_fix_check($cur_child, $fixes);
	}
}

$fixes = array();
// Start with top level categories.
$cats = $pines->entity_manager->get_entities(
		array('class' => com_sales_category),
		array('&',
			'tag' => array('com_sales', 'category')
		),
		array('!&',
			'isset' => 'parent'
		)
	);
foreach ((array) $cats as $cur_cat)
	com_sales__category_fix_check($cur_cat, $fixes);

$module = new module('com_sales', 'category/fixhier', 'content');
$module->fixes = $fixes;

?>