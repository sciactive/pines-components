<?php
/**
 * Create customer follow-ups.
 *
 * @package Pines
 * @subpackage com_customer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if (!$pines->config->com_customer->com_calendar)
	return;

/**
 * Cancel any customer follow-ups for a canceled/voided sale.
 *
 * @param object &$object The sale/return being saved.
 */
function com_customer__cancel_appointments(&$object) {
	global $pines;

	// Cancel any open customer follow-ups for this sale.
	$follow_ups = $pines->entity_manager->get_entities(
			array('class' => com_customer_interaction),
			array('&',
				'data' => array('status', 'open'),
				'ref' => array('sale', $object)
			)
		);
	foreach ($follow_ups as $cur_appt) {
		$cur_appt->status = 'canceled';
		$cur_appt->review_comments[] = format_date(time(), 'custom', 'n/j/y g:iA').': Returned ('.ucwords($cur_appt->status).')';
		if ($pines->config->com_customer->com_calendar) {
			$cur_appt->event->color = 'gainsboro';
			$cur_appt->event->information = $cur_appt->employee->name." (".ucwords($cur_appt->status).") \n";
			$cur_appt->event->information .= $cur_appt->comments."\n".implode("\n",$cur_appt->review_comments);
			$cur_appt->event->save();
		}
		$cur_appt->save();
	}
}

/**
 * Create customer follow-ups for any completed sale.
 *
 * @param array &$arguments Unused.
 * @param mixed $name Unused.
 * @param object &$object The sale being saved.
 */
function com_customer__check_sale(&$arguments, $name, &$object) {
	global $pines;

	if (!is_object($object) || !$pines->config->com_customer->follow_up)
		return;
	$websale = isset($object->user->guid) ? $object->user->is($object->customer) : $_SESSION['user']->is($object->customer);
	if (!$object->followed_up && isset($object->customer->guid) && !$websale && $object->status == 'paid') {
		$totals = array();
		foreach($object->products as $cur_product) {
			if ($cur_product['returned_quantity'] >= $cur_product['quantity'])
				continue;
			if (!isset($totals[$cur_product['salesperson']->guid]))
				$totals[$cur_product['salesperson']->guid] = 0;
			$totals[$cur_product['salesperson']->guid] += ($cur_product['quantity'] * $cur_product['price']);
		}
		if (empty($totals))
			return;
		$sales_rep = com_hrm_employee::factory((int) array_search(max($totals), $totals));
		if (!isset($sales_rep->guid))
			return;
		if (!$object->warehouse_items || $object->warehouse_complete) {
			$object->customer->schedule_follow_up($sales_rep, $object);
			$object->followed_up = true;
		} elseif (!$object->wh_followed_up) {
			$object->customer->schedule_follow_up($sales_rep, $object, true);
			$object->wh_followed_up = true;
		}
	}
	if ($object->status == 'voided')
		com_customer__cancel_appointments($object);
}

$pines->hook->add_callback('com_sales_sale->save', -10, 'com_customer__check_sale');

/**
 * Cancel customer follow-ups for any returned sale.
 *
 * @param array &$arguments Unused.
 * @param mixed $name Unused.
 * @param object &$object The sale being saved.
 */
function com_customer__check_return(&$arguments, $name, &$object) {
	global $pines;

	if (!is_object($object) || !$pines->config->com_customer->follow_up)
		return;
	// Add a check here for return->products == return->sale->products ???
	if ( $object->status == 'processed' && isset($object->sale->guid) &&
		($object->sale->followed_up || $object->sale->wh_followed_up) )
		com_customer__cancel_appointments($object->sale);
}

$pines->hook->add_callback('com_sales_return->save', -10, 'com_customer__check_return');

?>