<?php
/**
 * Save changes to a customer.
 *
 * @package Pines
 * @subpackage com_customer
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( empty($_REQUEST['username']) ) {
	display_error('Must specify username!');
	return;
}

if ( isset($_REQUEST['id']) ) {
	if ( !gatekeeper('com_customer/edit') ) {
		$config->user_manager->punt_user("You don't have necessary permission.", $config->template->url('com_customer', 'listcustomers', null, false));
		return;
	}
	$customer = $config->run_customer->get_customer($_REQUEST['id']);
    if (is_null($customer)) {
        display_error('Requested customer id is not accessible');
        return;
    }
} else {
	if ( !gatekeeper('com_customer/new') ) {
		$config->user_manager->punt_user("You don't have necessary permission.", $config->template->url('com_customer', 'listcustomers', null, false));
		return;
	}
	$customer = new entity;
    $customer->add_tag('com_customer', 'customer');
}

$customer->username = $_REQUEST['username'];
$customer->name = $_REQUEST['name'];
$customer->email = $_REQUEST['email'];
if (!empty($_REQUEST['password']))
    $customer->password = $_REQUEST['password'];
$customer->company = $_REQUEST['company'];
$customer->job_title = $_REQUEST['job_title'];
$customer->address_1 = $_REQUEST['address_1'];
$customer->address_2 = $_REQUEST['address_2'];
$customer->city = $_REQUEST['city'];
$customer->state = $_REQUEST['state'];
$customer->zip = $_REQUEST['zip'];
$customer->phone_home = $_REQUEST['phone_home'];
$customer->phone_work = $_REQUEST['phone_work'];
$customer->phone_cell = $_REQUEST['phone_cell'];
$customer->fax = $_REQUEST['fax'];

$customer->save();

display_notice('Saved customer ['.$customer->name.']');

$config->run_customer->list_customers();
?>