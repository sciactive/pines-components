<?php
/**
 * Provide a form to approve a countsheet.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zakhuber@gmail.com>
 * @copyright Zak Huber
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if (!gatekeeper('com_sales/approvecountsheet') )
	punt_user('You don\'t have necessary permission.', pines_url('com_sales', 'editcountsheet', array('id' => $_REQUEST['id'])));

if (!isset($_REQUEST['id'])) {
	pines_error('Requested countsheet id is not accessible.');
	$pines->com_sales->list_countsheets();
	return;
}
$entity = com_sales_countsheet::factory((int) $_REQUEST['id']);
/*
if (!$entity->final) {
	pines_notice('This countsheet has not been committed.');
	$pines->com_sales->list_countsheets();
	return;
}
*/
$entity->print_review();
?>