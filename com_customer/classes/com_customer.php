<?php
/**
 * com_customer class.
 *
 * @package Pines
 * @subpackage com_customer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * com_customer main class.
 *
 * Provides a CRM.
 *
 * @package Pines
 * @subpackage com_customer
 */
class com_customer extends component {
	/**
	 * Creates and attaches a module which lists companies.
	 */
	function list_companies() {
		$module = new module('com_customer', 'company/list', 'content');
	}
	
	/**
	 * Creates and attaches a module which lists customers.
	 */
	function list_customers() {
		$module = new module('com_customer', 'customer/list', 'content');
	}

	/**
	 * Add membership days to a customer for a product sale.
	 *
	 * @param array &$array The details array.
	 */
	function product_action_add_member_days(&$array) {
		global $pines;
		$array['sale']->customer->make_member();
		$days = 0;
		// Search through the membership day values.
		foreach($pines->config->com_customer->membervalues as $cur_value) {
			if (!is_numeric($cur_value))
				continue;
			$cur_value = (int) $cur_value;
			if ($array['name'] == "com_customer/add_member_days_$cur_value") {
				$days = (int) $cur_value;
				break;
			}
		}
		// If the sale is being voided, remove days.
		if ($array['type'] == 'voided')
			$days *= -1;
		// Add the days and save the customer.
		$array['sale']->customer->adjust_membership($days);
		if ($array['sale']->customer->save()) {
			pines_notice("Added $days days to {$array['sale']->customer->name}. Their membership now expires on ".format_date($array['sale']->customer->member_exp, 'date_long').'.');
		} else {
			pines_error("Error adding $days days to {$array['sale']->customer->name}.");
		}
	}

	/**
	 * Add points to a customer for a product sale.
	 *
	 * @param array &$array The details array.
	 */
	function product_action_add_points(&$array) {
		global $pines;
		$type = $array['sale']->customer->valid_member() ? 'member' : 'guest';
		$points = 0;
		if ($array['name'] == 'com_customer/add_points') {
			// Search through the right lookup table to find the divisor.
			$table = $array['sale']->customer->valid_member() ? $pines->config->com_customer->member_point_lookup : $pines->config->com_customer->guest_point_lookup;
			foreach ($table as $cur_price) {
				if ((float) preg_replace('/:.*$/', '', $cur_price) <= $array['price'])
					$high_price = $cur_price;
			}
			$divisor = (float) preg_replace('/^[^:]*:/', '', $high_price);
			if (!$divisor)
				return;
			$points = (int) round($array['price'] / $divisor);
		} else {
			// Search through the static point values.
			foreach($pines->config->com_customer->pointvalues as $cur_value) {
				if (!is_numeric($cur_value))
					continue;
				$cur_value = (int) $cur_value;
				if ($array['name'] == "com_customer/add_points_$cur_value") {
					$points = (int) $cur_value;
					break;
				}
			}
		}
		// If the sale is being voided, remove points.
		if ($array['type'] == 'voided')
			$points *= -1;
		// Add the points and save the customer.
		$array['sale']->customer->adjust_points($points);
		if ($array['sale']->customer->save()) {
			pines_notice("Added $points points to $type {$array['sale']->customer->name}.");
		} else {
			pines_error("Error adding $points points to $type {$array['sale']->customer->name}.");
		}
	}

	/**
	 * Transform a string to title case.
	 *
	 * @param string $string The string to transform.
	 * @param array $delimiters An array of strings used to delimit parts (words) of the string.
	 * @param array $exceptions An array of words which should not be changed.
	 * @return string The transformed string.
	 */
	function title_case($string, $delimiters = array(" ", "-", "O'", "Mc"), $exceptions = array("to", "a", "the", "of", "by", "and", "with", "II", "III", "IV", "V", "VI", "VII", "VIII", "IX", "X")) {
		/*
		* Exceptions in lower case are words you don't want converted
		* Exceptions all in upper case are any words you don't want converted to title case
		*   but should be converted to upper case, e.g.:
		*   king henry viii or king henry Viii should be King Henry VIII
		*/
		foreach ($delimiters as $delimiter){
			$words = explode($delimiter, $string);
			$newwords = array();
			foreach ($words as $word){
				if (in_array(strtoupper($word), $exceptions)){
					// check exceptions list for any words that should be in upper case
					$word = strtoupper($word);
				} elseif (!in_array($word, $exceptions)){
					// convert to uppercase
					$word = ucfirst($word);
				}
				array_push($newwords, $word);
			}
			$string = join($delimiter, $newwords);
		}
		return $string;
	}
}

?>