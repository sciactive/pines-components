<?php
/**
 * Begin a sale to a customer.
 *
 * @package Pines
 * @subpackage com_customertimer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/newsale') )
	punt_user('You don\'t have necessary permission.', pines_url('com_sales', 'editsale'));

$entity = com_sales_sale::factory();
$entity->customer = com_customer_customer::factory((int) $_REQUEST['customer']);
$entity->print_form();

?>