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
defined('P_RUN') or die('Direct access prohibited');

$interaction = com_customer_interaction::factory((int) $_REQUEST['id']);

if (!gatekeeper('com_customer/editinteraction'))
	punt_user(null, pines_url('com_customer', 'interaction/process', array('id' => $_REQUEST['id'])));

$pines->page->override = true;

if ((!gatekeeper('com_customer/manageinteractions') && !$interaction->user->is($_SESSION['user'])) ||
	!isset($interaction->guid) || $interaction->status == 'closed') {
	$pines->page->override_doc('false');
	return;
}

$interaction->status = $_REQUEST['status'];
$interaction->review_comments[] = $_SESSION['user']->name.': '.$_REQUEST['review_comments'];
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
	$interaction->event->save();
}
if ($interaction->save()) {
	$pines->page->override_doc('true');
} else {
	$pines->page->override_doc('false');
}

?>