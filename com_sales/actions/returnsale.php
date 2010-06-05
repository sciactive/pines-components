<?php
/**
 * Provide a form to return a sale.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/newreturn') )
	punt_user('You don\'t have necessary permission.', pines_url('com_sales', 'returnsale', array('id' => $_REQUEST['id'])));

$sale = com_sales_sale::factory((int) $_REQUEST['id']);

$entity = com_sales_return::factory();
if (isset($sale->guid))
	$entity->sale = $sale;
$entity->print_form();

?>