<?php
/**
 * Retrieve customer information, returning JSON.
 *
 * @package Pines
 * @subpackage com_customer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_customer/listcustomers') )
	punt_user(null, pines_url('com_customer', 'customer/info', $_REQUEST));

$pines->page->override = true;
header('Content-Type: application/json');

$customer = com_customer_customer::factory((int) $_REQUEST['id']);

if (!isset($customer->guid))
	return;

$json_struct = (object) array(
	'guid'			=> (int) $customer->guid,
	'username'		=> (string) $customer->username,
	'name'			=> (string) $customer->name,
	'name_first'	=> (string) $customer->name_first,
	'name_middle'	=> (string) $customer->name_middle,
	'name_last'		=> (string) $customer->name_last,
	'dob'			=> $customer->dob ? format_date($customer->dob, 'date_sort') : '',
	'ssn'			=> $customer->ssn ? $customer->ssn : '',
	'email'			=> (string) $customer->email,
	'company'		=> $customer->company->name ? $customer->company->name : '',
	'title'			=> (string) $customer->job_title,
	'address_1'		=> (string) $customer->address_1,
	'address_2'		=> (string) $customer->address_2,
	'city'			=> (string) $customer->city,
	'state'			=> (string) $customer->state,
	'zip'			=> (string) $customer->zip,
	'phone_home'	=> format_phone($customer->phone_home),
	'phone_work'	=> format_phone($customer->phone_work),
	'phone_cell'	=> format_phone($customer->phone_cell),
	'fax'			=> format_phone($customer->fax),
	'enabled'		=> (bool) $customer->has_tag('enabled'),
	'member'		=> (bool) $customer->member,
	'valid_member'	=> (bool) $customer->valid_member(),
	'member_exp'	=> $customer->member_exp ? format_date($customer->member_exp) : '',
	'points'		=> (int) $customer->points
);

$pines->page->override_doc(json_encode($json_struct));

?>