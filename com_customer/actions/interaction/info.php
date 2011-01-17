<?php
/**
 * Retreive customer interaction information, returning JSON.
 *
 * @package Pines
 * @subpackage com_customer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_customer/viewhistory') )
	punt_user(null, pines_url('com_customer', 'interaction/info', $_REQUEST));

$pines->page->override = true;

$interaction = com_customer_interaction::factory((int) $_REQUEST['id']);
if (!isset($interaction->guid))
	$pines->page->override_doc();

$json_struct = (object) array(
	'guid'				=> (int) $interaction->guid,
	'customer'			=> (string) $interaction->customer->name,
	'employee'			=> (string) $interaction->employee->name,
	'type'				=> (string) $interaction->type,
	'date'				=> format_date($interaction->action_date, 'full_sort'),
	'status'			=> (string) $interaction->status,
	'comments'			=> (string) $interaction->comments,
	'review_comments'	=> (array) $interaction->review_comments
);

$pines->page->override_doc(json_encode($json_struct));

?>