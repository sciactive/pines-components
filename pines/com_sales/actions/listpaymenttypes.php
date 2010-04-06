<?php
/**
 * List payment types.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/listpaymenttypes') )
	punt_user('You don\'t have necessary permission.', pines_url('com_sales', 'listpaymenttypes'));

$pines->com_sales->list_payment_types();
?>