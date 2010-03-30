<?php
/**
 * Add product actions for com_sales.
 *
 * @package Pines
 * @subpackage com_customer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

// Product actions to add points to a customer's profile.
if ($pines->com_customer->com_sales) {
	$pines->com_sales->product_actions[] = array(
		'type' => 'sold',
		'name' => 'com_customer/add_points',
		'cname' => 'Add Points',
		'description' => 'Add points to the customer\'s profile based on the price and the lookup table in configuration.',
		'callback' => array($pines->com_customer, 'product_action_add_points')
	);
	foreach($pines->config->com_customer->pointvalues as $cur_value) {
		$pines->com_sales->product_actions[] = array(
			'type' => 'sold',
			'name' => "com_customer/add_points_$cur_value",
			'cname' => "Add $cur_value Points",
			'description' => "Add $cur_value points to the customer's profile.",
			'callback' => array($pines->com_customer, 'product_action_add_points')
		);
	}
	foreach($pines->config->com_customer->membervalues as $cur_value) {
		$pines->com_sales->product_actions[] = array(
			'type' => 'sold',
			'name' => "com_customer/add_member_days_$cur_value",
			'cname' => "Add $cur_value Member Days",
			'description' => "Add $cur_value days of membership to the customer's profile. (Make them a member if they aren't one.)",
			'callback' => array($pines->com_customer, 'product_action_add_member_days')
		);
	}
}

?>