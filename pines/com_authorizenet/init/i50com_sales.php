<?php
/**
 * Add processing type for com_sales.
 *
 * @package Pines
 * @subpackage com_authorizenet
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

function com_authorizenet__payment_credit() {
	global $pines;
	$args = func_get_args();
	return call_user_func_array(array($pines->com_authorizenet, 'payment_credit'), $args);
}

$pines->config->com_sales->processing_types[] = array(
	'name' => 'com_authorizenet/credit',
	'cname' => 'Credit Card',
	'description' => 'Process credit card payment using Authorize.net.',
	'callback' => 'com_authorizenet__payment_credit'
);

?>