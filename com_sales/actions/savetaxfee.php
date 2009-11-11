<?php
/**
 * Save changes to a tax/fee.
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
	if ( !gatekeeper('com_sales/edittaxfee') ) {
		$config->user_manager->punt_user("You don't have necessary permission.", pines_url('com_sales', 'listtaxfees', null, false));
		return;
	}
	$tax_fee = $config->run_sales->get_tax_fee($_REQUEST['id']);
    if (is_null($tax_fee)) {
        display_error('Requested tax/fee id is not accessible');
        return;
    }
} else {
	if ( !gatekeeper('com_sales/newtaxfee') ) {
		$config->user_manager->punt_user("You don't have necessary permission.", pines_url('com_sales', 'listtaxfees', null, false));
		return;
	}
	$tax_fee = new entity;
    $tax_fee->add_tag('com_sales', 'tax_fee');
}

$tax_fee->name = $_REQUEST['name'];
$tax_fee->enabled = ($_REQUEST['enabled'] == 'ON' ? true : false);
$tax_fee->type = $_REQUEST['type'];
$tax_fee->rate = floatval($_REQUEST['rate']);
$tax_fee->groups = (is_array($_REQUEST['groups']) ? $_REQUEST['groups'] : array());

if (empty($tax_fee->name)) {
    $module = $config->run_sales->print_tax_fee_form('com_sales', 'savetaxfee');
    $module->entity = $tax_fee;
    display_error('Please specify a name.');
    return;
}
$test = $config->entity_manager->get_entities_by_data(array('name' => $tax_fee->name), array('com_sales', 'tax_fee'));
if (!empty($test) && $test[0]->guid != $_REQUEST['id']) {
    $module = $config->run_sales->print_tax_fee_form('com_sales', 'savetaxfee');
    $module->entity = $tax_fee;
    display_error('There is already a tax/fee with that name. Please choose a different name.');
    return;
}
if (empty($tax_fee->rate)) {
    $module = $config->run_sales->print_tax_fee_form('com_sales', 'savetaxfee');
    $module->entity = $tax_fee;
    display_error('Please specify a rate.');
    return;
}

if ($config->com_sales->global_tax_fees) {
    $tax_fee->ac = (object) array('other' => 1);
}

if ($tax_fee->save()) {
    display_notice('Saved tax/fee ['.$tax_fee->name.']');
} else {
    display_error('Error saving tax/fee. Do you have permission?');
}

$config->run_sales->list_tax_fees();
?>