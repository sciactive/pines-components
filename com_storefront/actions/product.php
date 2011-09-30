<?php
/**
 * Display a product page.
 *
 * @package Pines
 * @subpackage com_storefront
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if (!empty($_REQUEST['id'])) {
	$product = com_sales_product::factory((int) $_REQUEST['id']);
} else {
	$product = $pines->entity_manager->get_entity(
			array('class' => com_sales_product),
			array('&',
				'tag' => array('com_sales', 'product'),
				'data' => array(
					array('enabled', true),
					array('alias', $_REQUEST['a'])
				)
			)
		);
}

if (!isset($product->guid) || !$product->enabled || !$product->show_in_storefront)
	return 'error_404';

// Page title.
$pines->page->title_pre("$product->name - ");

$module = new module('com_storefront', 'breadcrumb', 'breadcrumbs');
$module->entity = $product;

$module = new module('com_storefront', 'product', 'content');
$module->entity = $product;

?>