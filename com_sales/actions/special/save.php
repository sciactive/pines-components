<?php
/**
 * Save changes to a special.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( isset($_REQUEST['id']) ) {
	if ( !gatekeeper('com_sales/editspecial') )
		punt_user(null, pines_url('com_sales', 'special/list'));
	$special = com_sales_special::factory((int) $_REQUEST['id']);
	if (!isset($special->guid)) {
		pines_error('Requested special id is not accessible.');
		return;
	}
} else {
	if ( !gatekeeper('com_sales/newspecial') )
		punt_user(null, pines_url('com_sales', 'special/list'));
	$special = com_sales_special::factory();
}

// General
$special->code = strtoupper($_REQUEST['code']);
$special->name = $_REQUEST['name'];
$special->enabled = ($_REQUEST['enabled'] == 'ON');
$special->before_tax = ($_REQUEST['before_tax'] == 'ON');
$special->apply_to_all = ($_REQUEST['apply_to_all'] == 'ON');
$special->per_ticket = (int) $_REQUEST['per_ticket'];
$discounts = (array) json_decode($_REQUEST['discounts']);
$special->discounts = array();
foreach ($discounts as $cur_discount) {
	if (!isset($cur_discount->values[0], $cur_discount->values[2]))
		continue;
	$type = $cur_discount->values[0];
	$qualifier = $cur_discount->values[1];
	$value = $cur_discount->values[2];
	switch ($type) {
		case 'order_amount':
		case 'order_percent':
			$special->discounts[] = array(
				'type' => $type,
				'value' => (float) $value
			);
			break;
		case 'product_amount':
		case 'product_percent':
		case 'item_amount':
		case 'item_percent':
			$product = $pines->com_sales->get_product_by_code($qualifier);
			if (!isset($product->guid)) {
				pines_notice("Couldn't find product with code $qualifier.");
				break;
			}
			$special->discounts[] = array(
				'type' => $type,
				'qualifier' => $product,
				'value' => (float) $value
			);
			break;
		case 'category_amount':
		case 'category_percent':
			$category = com_sales_category::factory((int) $qualifier);
			if (!isset($category->guid)) {
				pines_notice("Couldn't find category with ID $qualifier.");
				break;
			}
			$special->discounts[] = array(
				'type' => $type,
				'qualifier' => $category,
				'value' => (float) $value
			);
			break;
	}
}

// Conditions
$conditions = (array) json_decode($_REQUEST['conditions']);
$special->conditions = array();
foreach ($conditions as $cur_condition) {
	if (!isset($cur_condition->values[0], $cur_condition->values[1]))
		continue;
	$special->conditions[$cur_condition->values[0]] = $cur_condition->values[1];
}

// Requirements
$requirements = (array) json_decode($_REQUEST['requirements']);
$special->requirements = array();
foreach ($requirements as $cur_requirement) {
	if (!isset($cur_requirement->values[0], $cur_requirement->values[1]))
		continue;
	$type = $cur_requirement->values[0];
	$value = $cur_requirement->values[1];
	switch ($type) {
		case 'subtotal_eq':
		case 'subtotal_lt':
		case 'subtotal_gt':
			$special->requirements[] = array(
				'type' => $type,
				'value' => (float) $value
			);
			break;
		case 'has_product':
		case 'has_not_product':
			$product = $pines->com_sales->get_product_by_code($value);
			if (!isset($product->guid)) {
				pines_notice("Couldn't find product with code $value.");
				break;
			}
			$special->requirements[] = array(
				'type' => $type,
				'value' => $product
			);
			break;
		case 'has_category':
		case 'has_not_category':
			$category = com_sales_category::factory((int) $value);
			if (!isset($category->guid)) {
				pines_notice("Couldn't find category with ID $value.");
				break;
			}
			$special->requirements[] = array(
				'type' => $type,
				'value' => $category
			);
			break;
		case 'has_special':
		case 'has_not_special':
			if ($value == 'any') {
				$special->requirements[] = array(
					'type' => $type,
					'value' => 'any'
				);
			} else {
				$special_get = com_sales_special::factory((int) $value);
				if (!isset($special_get->guid)) {
					pines_notice("Couldn't find special with ID $value.");
					break;
				}
				$special->requirements[] = array(
					'type' => $type,
					'value' => $special_get
				);
			}
			break;
		case 'date_lt':
		case 'date_gt':
			$special->requirements[] = array(
				'type' => $type,
				'value' => strtotime($value)
			);
			break;
	}
}

if (empty($special->code)) {
	$special->print_form();
	pines_notice('Please specify a code.');
	return;
}
if (empty($special->name)) {
	$special->print_form();
	pines_notice('Please specify a name.');
	return;
}
$test = $pines->entity_manager->get_entity(array('class' => com_sales_special, 'skip_ac' => true), array('&', 'tag' => array('com_sales', 'special'), 'data' => array('code', $special->code)));
if (isset($test) && $test->guid != $_REQUEST['id']) {
	$special->print_form();
	pines_notice('There is already a special with that code. Please choose a different code.');
	return;
}

if ($pines->config->com_sales->global_specials)
	$special->ac->other = 1;

if ($special->save()) {
	pines_notice('Saved special ['.$special->name.']');
} else {
	pines_error('Error saving special. Do you have permission?');
}

pines_redirect(pines_url('com_sales', 'special/list'));

?>