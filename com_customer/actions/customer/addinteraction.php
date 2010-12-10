<?php
/**
 * Log a customer interaction.
 *
 * @package Pines
 * @subpackage com_customer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_customer/addinteraction') )
	punt_user(null, pines_url('com_customer', 'customer/addinteraction', array('id' => $_REQUEST['id'])));

$pines->page->override = true;

$customer = com_customer_customer::factory((int) $_REQUEST['id']);
if (!isset($customer->guid)) {
	$pines->page->override_doc('false');
	return;
}

$interaction = com_customer_interaction::factory();
$interaction->customer = $customer;
// Change the timezone to enter the event with the user's timezone.
date_default_timezone_set($_SESSION['user']->get_timezone());
$date = strtotime($_REQUEST['date']);
$date_month = date('n', $date);
$date_day = date('j', $date);
$date_year = date('Y', $date);
$time_hour = ($_REQUEST['time_ampm'] == 'am') ? $_REQUEST['time_hour'] : $_REQUEST['time_hour'] + 12;
$interaction->action_date = mktime($time_hour,$_REQUEST['time_minute'],0,$date_month,$date_day,$date_year);
$interaction->type = $_REQUEST['type'];
$interaction->comments = $_REQUEST['comments'];
$interaction->ac->other = 1;

if ($interaction->save()) {
	$pines->page->override_doc('true');
} else {
	$pines->page->override_doc('false');
}

?>