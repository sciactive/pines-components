<?php
/**
 * com_customer's loader.
 *
 * @package Pines
 * @subpackage com_customer
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

$config->run_customer = new com_customer;

foreach(array(1, 5, 10, 50, 60, 100, 120, 250, 500, 1000) as $cur_value) {
	$config->run_sales->product_actions[] = array(
		'type' => 'sold',
		'name' => "com_customer/add_points_$cur_value",
		'cname' => "Add $cur_value Points",
		'description' => "Add $cur_value points to the customer's account.",
		'callback' => array($config->run_customer, 'product_action_add_points')
	);
}

// Hook com_sales' customer form to give a link to mine.

?>