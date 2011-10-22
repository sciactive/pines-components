<?php
/**
 * View the cart.
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

if (!$pines->config->com_storefront->catalog_mode) {
	// Page title.
	$pines->page->title_pre("Cart - ");

	$module = new module('com_storefront', 'cart/view', 'content');
}

?>