<?php
/**
 * com_storefront's information.
 *
 * @package Pines
 * @subpackage com_storefront
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'name' => 'Storefront',
	'author' => 'SciActive',
	'version' => '1.0.0',
	'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
	'website' => 'http://www.sciactive.com',
	'short_description' => 'eCommerce storefront based on com_sales',
	'description' => 'Sell or display your products from com_sales online through a virtual storefront or catalog.',
	'depend' => array(
		'pines' => '<2',
		'service' => 'entity_manager&user_manager&icons&uploader',
		'component' => 'com_customer&com_sales&com_jquery&com_pgrid&com_pnotify&com_popeye&com_pform'
	),
	'abilities' => array(),
);

?>