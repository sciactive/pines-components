<?php
/**
 * List shippers.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zakhuber@gmail.com>
 * @copyright Zak Huber
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/listcountsheets') )
	punt_user('You don\'t have necessary permission.', pines_url('com_sales', 'listcountsheets'));

$pines->com_sales->list_countsheets();
?>