<?php
/**
 * Claim an accident using a customer's esp.
 *
 * @package Pines
 * @subpackage com_esp
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_esp/claim') )
	punt_user(null, pines_url('com_esp', 'list', array('id' => $_REQUEST['id'])));

// Retrieve the desired esp.
$esp = com_esp_plan::factory((int) $_REQUEST['id']);

if (!isset($esp->guid)) {
	pines_error('Requested ESP id is not accessible.');
	pines_redirect(pines_url('com_esp', 'list'));
	return;
}
if ($esp->status != 'registered') {
	pines_error('Requested ESP id is '.$esp->status);
	pines_redirect(pines_url('com_esp', 'list'));
	return;
}

// Claim the accident using the given ESP.
$esp->status = 'claimed';
$esp->claim_info = array('date' => time(), 'note' => htmlspecialchars($_REQUEST['comments']), 'user' => $_SESSION['user']);

if ($esp->save()) {
		pines_notice('Claimed ESP ['.$esp->customer->name.']');
} else {
	pines_error('Error saving ESP. Do you have permission?');
}

pines_redirect(pines_url('com_esp', 'list'));

?>