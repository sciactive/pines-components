<?php
/**
 * com_customer class.
 *
 * @package Pines
 * @subpackage com_customer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
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
	 * Whether to integrate with com_sales.
	 * 
	 * @var bool $com_sales
	 */
	var $com_sales;

	/**
	 * Check whether com_sales is installed and we should integrate with it.
	 *
	 * Places the result in $this->com_sales.
	 */
	function __construct() {
		global $pines;
		$this->com_sales = $pines->depend->check('component', 'com_sales');
	}

	/**
	 * Creates and attaches a module which lists companies.
	 */
	function list_companies() {
		global $pines;

		$pines->com_pgrid->load();

		$module = new module('com_customer', 'list_companies', 'content');
		if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
			$module->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_customer/list_companies'];

		$module->companies = $pines->entity_manager->get_entities(array('tags' => array('com_customer', 'company'), 'class' => com_customer_company));

		if ( empty($module->companies) ) {
			//$module->detach();
			display_notice('There are no companies.');
		}
	}
	
	/**
	 * Creates and attaches a module which lists customers.
	 */
	function list_customers() {
		global $pines;

		$pines->com_pgrid->load();

		$module = new module('com_customer', 'list_customers', 'content');
		if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
			$module->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_customer/list_customers'];

		$module->customers = $pines->entity_manager->get_entities(array('tags' => array('com_customer', 'customer'), 'class' => com_customer_customer));

		if ( empty($module->customers) ) {
			//$module->detach();
			display_notice('There are no customers.');
		}
	}

	/**
	 * Add membership days to a customer for a product sale.
	 *
	 * @param array $array The details array.
	 */
	function product_action_add_member_days($array) {
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
		// Add the days and save the customer.
		$array['sale']->customer->adjust_membership($days);
		if ($array['sale']->customer->save()) {
			display_notice("Added $days days to {$array['sale']->customer->name}. Their membership now expires on ".pines_date_format($array['sale']->customer->member_exp).'.');
		} else {
			display_error("Error adding $days days to {$array['sale']->customer->name}.");
		}
	}

	/**
	 * Add points to a customer for a product sale.
	 *
	 * @param array $array The details array.
	 */
	function product_action_add_points($array) {
		global $pines;
		$type = $array['sale']->customer->valid_member() ? 'member' : 'guest';
		$points = 0;
		if ($array['name'] == 'com_customer/add_points') {
			// Search through the right lookup table to find the divisor.
			$table = $array['sale']->customer->valid_member() ? $pines->config->com_customer->member_point_lookup : $pines->config->com_customer->guest_point_lookup;
			foreach ($table as $cur_price) {
				if ((float) preg_replace('/:.*$/', '', $cur_price) < $array['price'])
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
		// Add the points and save the customer.
		$array['sale']->customer->adjust_points($points);
		if ($array['sale']->customer->save()) {
			display_notice("Added $points points to $type {$array['sale']->customer->name}.");
		} else {
			display_error("Error adding $points points to $type {$array['sale']->customer->name}.");
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