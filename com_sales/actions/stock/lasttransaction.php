<?php
/**
 * Get the last transaction of a stock entry.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/managestock') )
	punt_user(null, pines_url('com_sales', 'stock/lasttransaction'));
$stock = com_sales_stock::factory((int) $_REQUEST['id']);
if (!isset($stock->guid)) {
	pines_error('Requested stock id is not accessible.');
	return;
}

$pines->page->override = true;
$pines->page->override_doc($stock->last_reason());

?>