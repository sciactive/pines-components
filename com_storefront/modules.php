<?php
/**
 * com_storefront's modules.
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
	'cart' => array(
		'cname' => 'Shopping Cart',
		'view' => 'cart/show',
	),
	'featured' => array(
		'cname' => 'Featured Item',
		'view' => 'modules/featured',
		'form' => 'modules/featured_form',
	),
);

?>