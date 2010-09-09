<?php
/**
 * Logout a user and return a JSON result.
 *
 * @package Pines
 * @subpackage com_customertimer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_customertimer/logout') )
	punt_user(null, pines_url('com_customertimer', 'status'));

$pines->page->override = true;

$return = false;

if (isset($_REQUEST['id'], $_REQUEST['floor'], $_REQUEST['station'])) {
	$customer = com_customertimer_customer::factory((int) $_REQUEST['id']);
	$floor = com_customertimer_floor::factory((int) $_REQUEST['floor']);
	if (!isset($customer->guid, $floor->guid)) {
		pines_error('Requested entries not found.');
		$return = false;
	} else {
		$return = $customer->com_customertimer_logout($floor, $_REQUEST['station']);
	}
}

$pines->page->override_doc(json_encode($return));

?>