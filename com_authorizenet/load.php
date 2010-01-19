<?php
/**
 * com_authorizenet's loader.
 *
 * @package Pines
 * @subpackage com_authorizenet
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Zak Huber <zakhuber@gmail.com>
 * @copyright Zak Huber
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

$config->run_sales->processing_types[] = array(
	'name' => 'com_authorizenet/credit',
	'cname' => 'Credit Card',
	'description' => 'Process credit card payment using Authorize.net.',
	'callback' => array($config->run_authorizenet, 'payment_credit')
);

?>