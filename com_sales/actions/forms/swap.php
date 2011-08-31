<?php
/**
 * Provide a form for swapping inventory.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/swapsale') )
	punt_user(null, pines_url('com_sales', 'forms/swap'));

$sale = com_sales_sale::factory((int) $_REQUEST['id']);
if (!isset($sale->guid)) {
	pines_error('Requested sale id is not accessible.');
	pines_redirect(pines_url('com_sales', 'sale/list'));
	return;
}

$sale->swap_form();

?>
