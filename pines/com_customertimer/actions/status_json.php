<?php
/**
 * Return a JSON structure of customers and their statuses.
 *
 * @package Pines
 * @subpackage com_customertimer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_customertimer/viewstatus') )
	punt_user('You don\'t have necessary permission.', pines_url('com_customertimer', 'status'));

$pines->page->override = true;

$logins = com_customertimer_login_tracker::factory();
$return = array();

foreach ($logins->customers as $cur_entry) {
	$session_info = $pines->com_customertimer->get_session_info($cur_entry['customer']);
	$return[] = (object) array(
		'guid' => $cur_entry['customer']->guid,
		'name' => $cur_entry['customer']->name,
		'login_time' => $cur_entry['customer']->com_customertimer->last_login,
		'points' => $cur_entry['customer']->points,
		'ses_minutes' => $session_info['minutes'],
		'ses_points' => $session_info['points'],
		'points_remain' => ($cur_entry['customer']->points - $session_info['points']),
		'station' => $cur_entry['station']
	);
}

$pines->page->override_doc(json_encode($return));

?>