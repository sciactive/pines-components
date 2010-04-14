<?php
/**
 * Add the default processing types.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

$pines->config->com_sales->processing_types[] = array(
	'name' => 'com_sales/instant',
	'cname' => 'Instant Processing',
	'description' => 'Approval and processing happen immediately. For example, a cash transaction.',
	'callback' => array($pines->com_sales, 'payment_instant')
);
$pines->config->com_sales->processing_types[] = array(
	'name' => 'com_sales/manager',
	'cname' => 'Manager Approval',
	'description' => 'Approval happens only after a manager verifies the payment. For example, a large cash transaction.',
	'callback' => array($pines->com_sales, 'payment_manager')
);

?>