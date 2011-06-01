<?php
/**
 * Save changes to a payment type.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( isset($_REQUEST['id']) ) {
	if ( !gatekeeper('com_sales/editpaymenttype') )
		punt_user(null, pines_url('com_sales', 'paymenttype/list'));
	$payment_type = com_sales_payment_type::factory((int) $_REQUEST['id']);
	if (!isset($payment_type->guid)) {
		pines_error('Requested payment type id is not accessible.');
		return;
	}
} else {
	if ( !gatekeeper('com_sales/newpaymenttype') )
		punt_user(null, pines_url('com_sales', 'paymenttype/list'));
	$payment_type = com_sales_payment_type::factory();
}

$payment_type->name = $_REQUEST['name'];
$payment_type->enabled = ($_REQUEST['enabled'] == 'ON');
if ($pines->config->com_sales->com_storefront)
	$payment_type->storefront = ($_REQUEST['storefront'] == 'ON');
$payment_type->kick_drawer = ($_REQUEST['kick_drawer'] == 'ON');
$payment_type->change_type = ($_REQUEST['change_type'] == 'ON');
$payment_type->minimum = (float) $_REQUEST['minimum'];
$payment_type->maximum = (float) $_REQUEST['maximum'];
$payment_type->allow_return = ($_REQUEST['allow_return'] == 'ON');
$payment_type->processing_type = $_REQUEST['processing_type'];

if (empty($payment_type->name)) {
	$payment_type->print_form();
	pines_notice('Please specify a name.');
	return;
}
$test = $pines->entity_manager->get_entity(array('class' => com_sales_payment_type, 'skip_ac' => true), array('&', 'tag' => array('com_sales', 'payment_type'), 'data' => array('name', $payment_type->name)));
if (isset($test) && $test->guid != $_REQUEST['id']) {
	$payment_type->print_form();
	pines_notice('There is already a payment type with that name. Please choose a different name.');
	return;
}
if (empty($payment_type->processing_type)) {
	$payment_type->print_form();
	pines_notice('Please specify a processing type.');
	return;
}
if (empty($payment_type->minimum))
	$payment_type->minimum = 0;
if (empty($payment_type->maximum))
	$payment_type->maximum = null;

if ($pines->config->com_sales->global_payment_types)
	$payment_type->ac->other = 1;

if ($payment_type->change_type) {
	$change_type = $pines->entity_manager->get_entity(
			array('class' => com_sales_payment_type),
			array('&',
				'tag' => array('com_sales', 'payment_type'),
				'data' => array('change_type', true)
			)
		);
	if (isset($change_type) && $change_type->guid != $_REQUEST['id']) {
		$change_type->change_type = false;
		if ($change_type->save()) {
			pines_notice("Change type changed from [{$change_type->name}] to [{$payment_type->name}].");
		} else {
			$payment_type->print_form();
			pines_error("There was an error while changing change type from {$change_type->name}. Do you have permission to edit the current change type?");
			return;
		}
	}
}

if ($payment_type->save()) {
	pines_notice('Saved payment type ['.$payment_type->name.']');
} else {
	pines_error('Error saving payment type. Do you have permission?');
}

pines_redirect(pines_url('com_sales', 'paymenttype/list'));

?>