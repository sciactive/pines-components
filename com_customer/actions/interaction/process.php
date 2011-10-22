<?php
/**
 * Process a customer interaction.
 *
 * @package Pines
 * @subpackage com_customer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

$interaction = com_customer_interaction::factory((int) $_REQUEST['id']);

if (!gatekeeper('com_customer/editinteraction'))
	punt_user(null, pines_url('com_customer', 'interaction/process', array('id' => $_REQUEST['id'])));

$pines->page->override = true;
header('Content-Type: application/json');

if ((!gatekeeper('com_customer/manageinteractions') && !$interaction->employee->is($_SESSION['user'])) ||
	!isset($interaction->guid)) {
	$pines->page->override_doc('false');
	return;
}
if ($interaction->status == 'closed'){
	$pines->page->override_doc('"closed"');
	return;
}
$comments = trim($_REQUEST['review_comments']);
if (empty($comments)) {
	$pines->page->override_doc('"comments"');
	return;
}

$interaction->status = $_REQUEST['status'];
$interaction->review_comments[] = $_SESSION['user']->name.' '.format_date(time(), 'custom', 'n/j/y g:iA').': '.$comments.'('.ucwords($interaction->status).')';
if ($pines->config->com_customer->com_calendar) {
	switch ($interaction->status) {
		case 'open':
			$interaction->event->color = 'greenyellow';
			break;
		case 'canceled':
			$interaction->event->color = 'gainsboro';
			break;
		case 'closed':
			$interaction->event->color = 'blue';
			break;
	}
	$interaction->event->information = $interaction->employee->name." (".ucwords($interaction->status).") \n";
	$interaction->event->information .= $interaction->comments."\n".implode("\n",$interaction->review_comments);
	$interaction->event->save();
}
if ($interaction->save()) {
	$pines->page->override_doc('true');
} else {
	$pines->page->override_doc('false');
}

?>