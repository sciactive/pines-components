<?php
/**
 * Save changes to a manufacturer.
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
	if ( !gatekeeper('com_sales/editmanufacturer') ) {
		$config->user_manager->punt_user("You don't have necessary permission.", pines_url('com_sales', 'listmanufacturers', null, false));
		return;
	}
	$manufacturer = $config->run_sales->get_manufacturer($_REQUEST['id']);
    if (is_null($manufacturer)) {
        display_error('Requested manufacturer id is not accessible');
        return;
    }
} else {
	if ( !gatekeeper('com_sales/newmanufacturer') ) {
		$config->user_manager->punt_user("You don't have necessary permission.", pines_url('com_sales', 'listmanufacturers', null, false));
		return;
	}
	$manufacturer = new entity;
    $manufacturer->add_tag('com_sales', 'manufacturer');
}

$manufacturer->name = $_REQUEST['name'];
$manufacturer->email = $_REQUEST['email'];
$manufacturer->address_1 = $_REQUEST['address_1'];
$manufacturer->address_2 = $_REQUEST['address_2'];
$manufacturer->city = $_REQUEST['city'];
$manufacturer->state = $_REQUEST['state'];
$manufacturer->zip = $_REQUEST['zip'];
$manufacturer->phone_work = $_REQUEST['phone_work'];
$manufacturer->fax = $_REQUEST['fax'];

if (empty($manufacturer->name)) {
    $module = $config->run_sales->print_manufacturer_form('Editing Manufacturer', 'com_sales', 'savemanufacturer');
    $module->entity = $manufacturer;
    display_error('Please specify a name.');
    return;
}
$test = $config->entity_manager->get_entities_by_data(array('name' => $manufacturer->name), array('com_sales', 'manufacturer'));
if (!empty($test) && $test[0]->guid != $_REQUEST['id']) {
    $module = $config->run_sales->print_manufacturer_form('Editing Manufacturer', 'com_sales', 'savemanufacturer');
    $module->entity = $manufacturer;
    display_error('There is already a manufacturer with that name. Please choose a different name.');
    return;
}

$manufacturer->save();

if ($config->com_sales->global_manufacturers) {
    unset($manufacturer->uid);
    unset($manufacturer->gid);
}

$manufacturer->save();

display_notice('Saved manufacturer ['.$manufacturer->name.']');

$config->run_sales->list_manufacturers();
?>