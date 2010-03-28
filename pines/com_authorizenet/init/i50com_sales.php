<?php
/**
 * Add processing type for com_sales.
 *
 * @package Pines
 * @subpackage com_authorizenet
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zakhuber@gmail.com>
 * @copyright Zak Huber
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

$pines->com_sales->processing_types[] = array(
	'name' => 'com_authorizenet/credit',
	'cname' => 'Credit Card',
	'description' => 'Process credit card payment using Authorize.net.',
	'callback' => array($pines->com_authorizenet, 'payment_credit')
);

?>