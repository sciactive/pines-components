<?php
/**
 * Provide a form for swapping salespeople.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/swapsalesrep') )
	punt_user(null, pines_url('com_sales', 'forms/salesrep'));

if ($_REQUEST['type'] == 'sale') {
	$entity = com_sales_sale::factory((int) $_REQUEST['id']);
} elseif ($_REQUEST['type'] == 'return') {
	$entity = com_sales_return::factory((int) $_REQUEST['id']);
}

if (!isset($entity->guid)) {
	pines_error('Requested sale id is not accessible.');
	return;
}

$entity->salesrep_form();

?>