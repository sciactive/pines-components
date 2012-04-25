<?php
/**
 * Provide a form for changing a product.
 *
 * @package Components\sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/changeproduct') )
	punt_user(null, pines_url('com_sales', 'forms/changeproduct'));

$sale = com_sales_sale::factory((int) $_REQUEST['id']);
if (!isset($sale->guid)) {
	pines_error('Requested sale id is not accessible.');
	pines_redirect(pines_url('com_sales', 'sale/list'));
	return;
}

$sale->change_product_form();

?>