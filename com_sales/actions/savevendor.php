<?php
/**
 * Save changes to a vendor.
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
	if ( !gatekeeper('com_sales/editvendor') ) {
		$config->user_manager->punt_user("You don't have necessary permission.", pines_url('com_sales', 'listvendors', null, false));
		return;
	}
	$vendor = $config->run_sales->get_vendor($_REQUEST['id']);
    if (is_null($vendor)) {
        display_error('Requested vendor id is not accessible');
        return;
    }
} else {
	if ( !gatekeeper('com_sales/newvendor') ) {
		$config->user_manager->punt_user("You don't have necessary permission.", pines_url('com_sales', 'listvendors', null, false));
		return;
	}
	$vendor = new entity;
    $vendor->add_tag('com_sales', 'vendor');
}

$vendor->name = $_REQUEST['name'];
$vendor->email = $_REQUEST['email'];
$vendor->address_1 = $_REQUEST['address_1'];
$vendor->address_2 = $_REQUEST['address_2'];
$vendor->city = $_REQUEST['city'];
$vendor->state = $_REQUEST['state'];
$vendor->zip = $_REQUEST['zip'];
$vendor->phone_work = $_REQUEST['phone_work'];
$vendor->fax = $_REQUEST['fax'];
$vendor->account_number = $_REQUEST['account_number'];
$vendor->client_username = $_REQUEST['client_username'];
$vendor->client_password = $_REQUEST['client_password'];
$vendor->client_rep_name = $_REQUEST['client_rep_name'];
$vendor->client_email = $_REQUEST['client_email'];
$vendor->client_web_address = $_REQUEST['client_web_address'];
$vendor->online_web_address = $_REQUEST['online_web_address'];
$vendor->online_customer_id = $_REQUEST['online_customer_id'];
$vendor->online_username = $_REQUEST['online_username'];
$vendor->online_password = $_REQUEST['online_password'];
$vendor->terms = $_REQUEST['terms'];
$vendor->comments = $_REQUEST['comments'];

if (empty($vendor->name)) {
    $module = $config->run_sales->print_vendor_form('Editing Vendor', 'com_sales', 'savevendor');
    $module->entity = $vendor;
    display_error('Please specify a name.');
    return;
}
$test = $config->entity_manager->get_entities_by_data(array('name' => $vendor->name), array('com_sales', 'vendor'));
if (!empty($test) && $test[0]->guid != $_REQUEST['id']) {
    $module = $config->run_sales->print_vendor_form('Editing Vendor', 'com_sales', 'savevendor');
    $module->entity = $vendor;
    display_error('There is already a vendor with that name. Please choose a different name.');
    return;
}

if ($config->com_sales->global_vendors) {
    $vendor->ac = (object) array('other' => 1);
}

$vendor->save();

display_notice('Saved vendor ['.$vendor->name.']');

$config->run_sales->list_vendors();
?>