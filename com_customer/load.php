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

// Product actions to add points to a customer's profile.
if ($config->run_customer->com_sales) {
	foreach(explode(',', $config->com_customer->pointvalues) as $cur_value) {
		if (!is_numeric($cur_value))
			continue;
		$cur_value = (int) $cur_value;
		$config->run_sales->product_actions[] = array(
			'type' => 'sold',
			'name' => "com_customer/add_points_$cur_value",
			'cname' => "Add $cur_value Points",
			'description' => "Add $cur_value points to the customer's profile.",
			'callback' => array($config->run_customer, 'product_action_add_points')
		);
	}
}

?>