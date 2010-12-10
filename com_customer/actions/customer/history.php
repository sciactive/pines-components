<?php
/**
 * View a customer's history.
 *
 * @package Pines
 * @subpackage com_customer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_customer/viewhistory') )
	punt_user(null, pines_url('com_customer', 'customer/history', array('id' => $_REQUEST['id'])));

$customer = com_customer_customer::factory((int) $_REQUEST['id']);
if (!isset($customer->guid)) {
	pines_error('Requested Customer id is not accessible.');
	return;
}

$customer->print_history();

?>