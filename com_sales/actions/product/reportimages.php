<?php
/**
 * Report product images.
 *
 * @package Components\sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/listproducts') )
	punt_user(null, pines_url('com_sales', 'product/reportimages'));

$module = new module('com_sales', 'product/report_images', 'content');
$module->section1 = $module->section2 = $module->section3 = $module->section4 = $module->complete = array();

$products = (array) $pines->entity_manager->get_entities(
		array('class' => com_sales_product),
		array('&',
			'tag' => array('com_sales', 'product')
		)
	);

foreach ($products as $cur_product) {
	if ($cur_product->images && file_exists($cur_product->thumbnail)) {
		$all = true;
		foreach ($cur_product->images as $cur_image) {
			if (!file_exists($cur_image['file']) || !file_exists($cur_image['thumbnail'])) {
				$all = false;
				break;
			}
		}
		if ($all) {
			$module->complete[] = $cur_product;
			continue;
		}
	}
	if ($cur_product->enabled) {
		if ($cur_product->show_in_storefront)
			$module->section1[] = $cur_product;
		else
			$module->section2[] = $cur_product;
	} else {
		if ($cur_product->show_in_storefront)
			$module->section3[] = $cur_product;
		else
			$module->section4[] = $cur_product;
	}
}

?>