<?php
/**
 * Provide a form to return a sale.
 *
 * @package Components\sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/newreturnwsale') )
	punt_user(null, pines_url('com_sales', 'sale/return', array('id' => $_REQUEST['id'])));

$sale = com_sales_sale::factory((int) $_REQUEST['id']);

$entity = com_sales_return::factory();
if (isset($sale->guid))
	$entity->attach_sale($sale);
$entity->print_form();

?>