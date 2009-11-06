<?php
/**
 * Save changes to a customer.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( isset($_REQUEST['id']) ) {
	if ( !gatekeeper('com_sales/editcustomer') ) {
		$config->user_manager->punt_user("You don't have necessary permission.", pines_url('com_sales', 'listcustomers', null, false));
		return;
	}
	$customer = $config->run_sales->get_customer($_REQUEST['id']);
    if (is_null($customer)) {
        display_error('Requested customer id is not accessible');
        return;
    }
} else {
	if ( !gatekeeper('com_sales/newcustomer') ) {
		$config->user_manager->punt_user("You don't have necessary permission.", pines_url('com_sales', 'listcustomers', null, false));
		return;
	}
	$customer = new entity;
    $customer->add_tag('com_sales', 'customer');
}

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

if (empty($customer->name)) {
    $module = $config->run_sales->print_customer_form('Editing Customer', 'com_sales', 'savecustomer');
    $module->entity = $customer;
    display_error('Please specify a name.');
    return;
}
$test = $config->entity_manager->get_entities_by_data(array('name' => $customer->name), array('com_sales', 'customer'));
if (!empty($test) && $test[0]->guid != $_REQUEST['id']) {
    $module = $config->run_sales->print_customer_form('Editing Customer', 'com_sales', 'savecustomer');
    $module->entity = $customer;
    display_error('There is already a customer with that name. Please choose a different name.');
    return;
}

$customer->save();

if ($config->com_sales->global_customers) {
    unset($customer->uid);
    unset($customer->gid);
}

$customer->save();

display_notice('Saved customer ['.$customer->name.']');

$config->run_sales->list_customers();
?>