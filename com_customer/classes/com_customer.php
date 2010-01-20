<?php
/**
 * com_customer class.
 *
 * @package Pines
 * @subpackage com_customer
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
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
		global $config;
		$this->com_sales = $config->depend->check('component', 'com_sales');
	}

	/**
	 * Creates and attaches a module which lists companies.
	 */
	function list_companies() {
		global $config;

		$pgrid = new module('system', 'pgrid.default', 'head');
		$pgrid->icons = true;

		$module = new module('com_customer', 'list_companies', 'content');
		if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
			$module->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_customer/list_companies'];

		$module->companies = $config->entity_manager->get_entities(array('tags' => array('com_customer', 'company'), 'class' => com_customer_company));

		if ( empty($module->companies) ) {
			$pgrid->detach();
			$module->detach();
			display_notice("There are no companies.");
		}
	}
	
	/**
	 * Creates and attaches a module which lists customers.
	 */
	function list_customers() {
		global $config;

		$pgrid = new module('system', 'pgrid.default', 'head');
		$pgrid->icons = true;

		$module = new module('com_customer', 'list_customers', 'content');
		if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
			$module->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_customer/list_customers'];

		$module->customers = $config->entity_manager->get_entities(array('tags' => array('com_customer', 'customer'), 'class' => com_customer_customer));

		if ( empty($module->customers) ) {
			$pgrid->detach();
			$module->detach();
			display_notice("There are no customers.");
		}
	}

	/**
	 * Add points to a customer for a product sale.
	 *
	 * @param array $array The details array.
	 */
	function product_action_add_points($array) {
		global $config;
		foreach(explode(',', $config->com_customer->pointvalues) as $cur_value) {
			if (!is_numeric($cur_value))
				continue;
			$cur_value = intval($cur_value);
			if ($array['name'] == "com_customer/add_points_$cur_value") {
				$array['sale']->customer->adjust_points($cur_value);
				$array['sale']->customer->save();
			}
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