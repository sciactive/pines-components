<?php
/**
 * com_storefront's modules.
 *
 * @package Components\storefront
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
		'description' => 'View your shopping cart.',
		'view' => 'cart/show',
		'type' => 'module imodule',
	),
	'featured' => array(
		'cname' => 'Featured Item',
		'description' => 'Display featured storefront items.',
		'view' => 'modules/featured',
		'form' => 'modules/featured_form',
		'type' => 'module imodule',
	),
);

?>