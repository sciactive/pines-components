<?php
/**
 * Save changes to a tax/fee.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( isset($_REQUEST['id']) ) {
	if ( !gatekeeper('com_sales/edittaxfee') )
		punt_user('You don\'t have necessary permission.', pines_url('com_sales', 'listtaxfees', null, false));
	$tax_fee = com_sales_tax_fee::factory((int) $_REQUEST['id']);
	if (is_null($tax_fee->guid)) {
		display_error('Requested tax/fee id is not accessible');
		return;
	}
} else {
	if ( !gatekeeper('com_sales/newtaxfee') )
		punt_user('You don\'t have necessary permission.', pines_url('com_sales', 'listtaxfees', null, false));
	$tax_fee = com_sales_tax_fee::factory();
}

$tax_fee->name = $_REQUEST['name'];
$tax_fee->enabled = ($_REQUEST['enabled'] == 'ON');
$tax_fee->type = $_REQUEST['type'];
$tax_fee->rate = floatval($_REQUEST['rate']);
$tax_fee->locations = array();
if (is_array($_REQUEST['locations'])) {
	foreach ($_REQUEST['locations'] as $cur_location_guid) {
		$cur_location = group::factory((int) $cur_location_guid);
		if (isset($cur_location->guid))
			$tax_fee->locations[] = $cur_location;
	}
}

if (empty($tax_fee->name)) {
	$tax_fee->print_form();
	display_notice('Please specify a name.');
	return;
}
$test = $pines->entity_manager->get_entity(array('data' => array('name' => $tax_fee->name), 'tags' => array('com_sales', 'tax_fee'), 'class' => com_sales_tax_fee));
if (isset($test) && $test->guid != $_REQUEST['id']) {
	$tax_fee->print_form();
	display_notice('There is already a tax/fee with that name. Please choose a different name.');
	return;
}
if (empty($tax_fee->rate)) {
	$tax_fee->print_form();
	display_notice('Please specify a rate.');
	return;
}

if ($pines->config->com_sales->global_tax_fees)
	$tax_fee->ac->other = 1;

if ($tax_fee->save()) {
	display_notice('Saved tax/fee ['.$tax_fee->name.']');
} else {
	display_error('Error saving tax/fee. Do you have permission?');
}

$pines->com_sales->list_tax_fees();
?>