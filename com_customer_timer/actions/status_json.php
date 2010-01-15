<?php
/**
 * Return a JSON structure of customers and their statuses.
 *
 * @package Pines
 * @subpackage com_customer_timer
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_customer_timer/viewstatus') ) {
	$config->user_manager->punt_user("You don't have necessary permission.", pines_url('com_customer_timer', 'status', null, false));
	return;
}

$page->override = true;

$logins = com_customer_timer_login_tracker::factory();
$return = array();

foreach ($logins->customers as $cur_customer) {
	$session_info = $config->run_customer_timer->get_session_info($cur_customer);
	$return[] = (object) array(
		'guid' => $cur_customer->guid,
		'name' => $cur_customer->name,
		'login_time' => $cur_customer->com_customer_timer->last_login,
		'points' => $cur_customer->points,
		'ses_minutes' => $session_info['minutes'],
		'ses_points' => $session_info['points'],
		'points_remain' => ($cur_customer->points - $session_info['points'])
	);
}

$page->override_doc(json_encode($return));

?>