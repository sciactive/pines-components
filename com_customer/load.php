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

// Product actions to add points to a customer's account.
foreach(explode(',', $config->com_customer->pointvalues) as $cur_value) {
	if (!is_numeric($cur_value))
		continue;
	$cur_value = intval($cur_value);
	$config->run_sales->product_actions[] = array(
		'type' => 'sold',
		'name' => "com_customer/add_points_$cur_value",
		'cname' => "Add $cur_value Points",
		'description' => "Add $cur_value points to the customer's account.",
		'callback' => array($config->run_customer, 'product_action_add_points')
	);
}

// Hook com_sales' customer form to give a link to mine.
$config->hook->add_callback('$config->run_sales->print_customer_form', -1, array($config->run_customer, 'hook_customer_view'));

?>