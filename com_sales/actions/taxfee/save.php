<?php
/**
 * Save changes to a tax/fee.
 *
 * @package Components\sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( isset($_REQUEST['id']) ) {
	if ( !gatekeeper('com_sales/edittaxfee') )
		punt_user(null, pines_url('com_sales', 'taxfee/list'));
	$tax_fee = com_sales_tax_fee::factory((int) $_REQUEST['id']);
	if (!isset($tax_fee->guid)) {
		pines_error('Requested tax/fee id is not accessible.');
		return;
	}
} else {
	if ( !gatekeeper('com_sales/newtaxfee') )
		punt_user(null, pines_url('com_sales', 'taxfee/list'));
	$tax_fee = com_sales_tax_fee::factory();
}

$tax_fee->name = $_REQUEST['name'];
$tax_fee->enabled = ($_REQUEST['enabled'] == 'ON');
$tax_fee->type = $_REQUEST['type'];
$tax_fee->rate = (float) $_REQUEST['rate'];
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
	pines_notice('Please specify a name.');
	return;
}
$test = $pines->entity_manager->get_entity(array('class' => com_sales_tax_fee, 'skip_ac' => true), array('&', 'tag' => array('com_sales', 'tax_fee'), 'data' => array('name', $tax_fee->name)));
if (isset($test) && $test->guid != $_REQUEST['id']) {
	$tax_fee->print_form();
	pines_notice('There is already a tax/fee with that name. Please choose a different name.');
	return;
}
if (empty($tax_fee->rate)) {
	$tax_fee->print_form();
	pines_notice('Please specify a rate.');
	return;
}

if ($pines->config->com_sales->global_tax_fees)
	$tax_fee->ac->other = 1;

if ($tax_fee->save()) {
	pines_notice('Saved tax/fee ['.$tax_fee->name.']');
} else {
	pines_error('Error saving tax/fee. Do you have permission?');
}

pines_redirect(pines_url('com_sales', 'taxfee/list'));

?>