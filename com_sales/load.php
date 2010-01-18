<?php
/**
 * com_sales's loader.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

$config->run_sales->processing_types[] = array(
	'name' => 'com_sales/instant',
	'cname' => 'Instant Processing',
	'description' => 'Approval and processing happen immediately. For example, a cash transaction.',
	'callback' => array($config->run_sales, 'payment_instant')
);
$config->run_sales->processing_types[] = array(
	'name' => 'com_sales/manager',
	'cname' => 'Manager Approval',
	'description' => 'Approval happens only after a manager verifies the payment. For example, a large cash transaction.',
	'callback' => array($config->run_sales, 'payment_manager')
);

?>