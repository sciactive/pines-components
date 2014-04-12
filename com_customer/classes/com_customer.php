<?php
/**
 * com_customer class.
 *
 * @package Components\customer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

/**
 * com_customer main class.
 *
 * Provides a CRM.
 *
 * @package Components\customer
 */
class com_customer extends component {
	/**
	 * Whether the customer selector JavaScript has been loaded.
	 * @access private
	 * @var bool $js_loaded_cust
	 */
	private $js_loaded_cust = false;
	/**
	 * Whether the company selector JavaScript has been loaded.
	 * @access private
	 * @var bool $js_loaded_comp
	 */
	private $js_loaded_comp = false;

	/**
	 * Creates and attaches a module which lists companies.
	 * @return module The module.
	 */
	function list_companies() {
		$module = new module('com_customer', 'company/list', 'content');
		return $module;
	}
	
	/**
	 * Creates and attaches a module which lists customers.
	 * @return module The module.
	 */
	function list_customers() {
		$module = new module('com_customer', 'customer/list', 'content');
		return $module;
	}

	/**
	 * Load the company selector.
	 *
	 * This will place the required scripts into the document's head section.
	 */
	function load_company_select() {
		if (!$this->js_loaded_comp) {
			$module = new module('com_customer', 'company/select', 'head');
			$module->render();
			$this->js_loaded_comp = true;
		}
	}

	/**
	 * Load the customer selector.
	 *
	 * This will place the required scripts into the document's head section.
	 */
	function load_customer_select() {
		global $pines;
		if (!$this->js_loaded_cust) {
			if ($pines->config->compress_cssjs) {
				$file_root = htmlspecialchars($_SERVER['DOCUMENT_ROOT'].$pines->config->location);
				$js = (is_array($pines->config->loadcompressedjs)) ? $pines->config->loadcompressedjs : array();
                                if ($pines->config->com_customer->no_autocomplete) {
                                    $js[] = $js[] =  $file_root.'components/com_customer/includes/'.($pines->config->debug_mode ? 'jquery.customerselect.noauto.js' : 'jquery.customerselect.noauto.min.js');
                                } else {
                                    $js[] =  $file_root.'components/com_customer/includes/'.($pines->config->debug_mode ? 'jquery.customerselect.js' : 'jquery.customerselect.min.js');
                                }
				
				// This component needs a dynamic variable.
				$auto_url = pines_url('com_customer', 'customer/search');
				if (preg_match('/\?/', $auto_url)) {
					$js[] =  $file_root.'components/com_customer/includes/jquery.customerselect.longurl.js';
				} else {
					$js[] =  $file_root.'components/com_customer/includes/jquery.customerselect.shorturl.js';
				}
				
				$pines->config->loadcompressedjs = $js;
			} else {
				$module = new module('com_customer', 'customer/select', 'head');
				$module->render();
			}
			$this->js_loaded_cust = true;
		}
	}

	/**
	 * Add membership days to a customer for a product sale.
	 *
	 * @param array &$array The details array.
	 */
	function product_action_add_member_days(&$array) {
		global $pines;
		$array['ticket']->customer->make_member();
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
		$array['ticket']->customer->adjust_membership($days);
		if ($array['ticket']->customer->save()) {
			pines_notice("Added $days days to {$array['ticket']->customer->name}. Their membership now expires on ".format_date($array['ticket']->customer->member_exp, 'date_long').'.');
		} else {
			pines_error("Error adding $days days to {$array['ticket']->customer->name}.");
		}
	}

	/**
	 * Add points to a customer for a product sale.
	 *
	 * @param array &$array The details array.
	 */
	function product_action_add_points(&$array) {
		global $pines;
		$type = $array['ticket']->customer->valid_member() ? 'member' : 'guest';
		$points = 0;
		if ($array['name'] == 'com_customer/add_points') {
			// Search through the right lookup table to find the divisor.
			$table = $array['ticket']->customer->valid_member() ? $pines->config->com_customer->member_point_lookup : $pines->config->com_customer->guest_point_lookup;
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
		$array['ticket']->customer->adjust_points($points);
		if ($array['ticket']->customer->save()) {
			pines_notice("Added $points points to $type {$array['ticket']->customer->name}.");
		} else {
			pines_error("Error adding $points points to $type {$array['ticket']->customer->name}.");
		}
	}
	
	/**
	 * Check that an ssn is unique.
	 * 
	 * The ID of a user can be given so that user is excluded when checking if
	 * the ssn is already in use.
	 * 
	 * Wrote this mainly for quick ajax testing of the ssn for user sign up on
	 * an application.
	 * 
	 * @param string $ssn The ssn to check.
	 * @param int $id The GUID of the user for which the name is being checked.
	 * @return array An associative array with a boolean 'result' entry and a 'message' entry.
	 */
	public function check_ssn($ssn, $id = null) {
		global $pines;
		
		if (empty($ssn))
			return array('result' => false, 'message' => 'Please specify an SSN.');
		if (!preg_match('/^((?!000)(?!666)(?:[0-8]\d{2}|7[0-2][0-9]|73[0-3]|7[5-6][0-9]|77[0-2]))-((?!00)\d{2})-((?!0000)\d{4})$/i', $ssn))
			return array('result' => false, 'message' => 'SSN must be a valid and correctly formatted SSN.');
		
		// Remove Dashes
		$ssn = str_replace('-', '', $ssn);
		
		$selector = array('&',
				'strict' => array('ssn', $ssn)
			);
		if (isset($id) && $id > 0)
			$selector['!guid'] = $id;
		$test = $pines->entity_manager->get_entity(
				array('class' => com_customer_customer, 'skip_ac' => true),
				$selector
			);
		if (isset($test->guid))
			return array('result' => false, 'message' => "That SSN is already registered.");

		return array('result' => true, 'message' => (isset($id) ? 'SSN is valid.' : 'SSN is valid!'));
	}
	

	/**
	 * Make new users customers as well.
	 *
	 * @param array &$array Arguments.
	 * @param string $name Hook name.
	 * @param object &$object The user being saved.
	 */
	function save_user(&$array, $name, &$object) {
		global $pines;
		if ($object->has_tag('com_customer', 'customer')) {
			// If the secret is unset, it means they verified their email address.
			if (!isset($object->secret)) {
				if ($object->com_customer__unconfirmed_groups) {
					// We should put them in the default customer groups.
					$object->groups = (array) $pines->entity_manager->get_entities(
							array('class' => group, 'skip_ac' => true),
							array('&',
								'tag' => array('com_user', 'group'),
								'data' => array('default_customer_secondary', true)
							)
						);
					unset($object->com_customer__unconfirmed_groups);
				}
				unset($object->com_customer__unconfirmed);
			}
			return;
		}
		if (
				$pines->config->com_customer->new_users ||
				(
						$pines->config->com_customer->reg_users &&
						$pines->depend->check('option', 'com_user') &&
						$pines->depend->check('action', 'registeruser')
				)
			) {
			$object->add_tag('com_customer', 'customer');
			$object->points = 0;
			$object->peak_points = 0;
			$object->total_points = 0;
		}
	}

	/**
	 * Print a form to select date timespan.
	 *
	 * @param bool $all_time Currently searching all records or a timespan.
	 * @param string $start The current starting date of the timespan.
	 * @param string $end The current ending date of the timespan.
	 * @return module The form's module.
	 */
	public function date_select_form($all_time = false, $start = null, $end = null) {
		global $pines;
		$pines->page->override = true;

		$module = new module('com_customer', 'forms/date_selector', 'content');
		$module->all_time = $all_time;
		$module->start_date = $start;
		$module->end_date = $end;

		$pines->page->override_doc($module->render());
		return $module;
	}

	/**
	 * Print a form to select a location.
	 *
	 * @param int $location The currently set location to search in.
	 * @param bool $descendants Whether to show descendant locations.
	 * @return module The form's module.
	 */
	public function location_select_form($location = null, $descendants = false) {
		global $pines;
		$pines->page->override = true;

		$module = new module('com_customer', 'forms/location_selector', 'content');
		if (!isset($location)) {
			$module->location = $_SESSION['user']->group->guid;
		} else {
			$module->location = $location;
		}
		$module->descendants = $descendants;

		$pines->page->override_doc($module->render());
		return $module;
	}
	
	/**
	 * Transform a string to title case.
	 *
	 * @param string $string The string to transform.
	 * @param array $delimiters An array of strings used to delimit parts (words) of the string.
	 * @param array $exceptions An array of words which should not be changed.
	 * @return string The transformed string.
	 */
	public function title_case($string, $delimiters = array(' ', '-', 'O\'', 'Mc'), $exceptions = array('to', 'a', 'the', 'of', 'by', 'and', 'with', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X')) {
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
				$newwords[] = $word;
			}
			$string = join($delimiter, $newwords);
		}
		return $string;
	}
	
	/**
	 * Sort users.
	 * @param group $a User.
	 * @param group $b User.
	 * @return bool User order.
	 */
	private function sort_users($a, $b) {
		$aname = empty($a->name) ? $a->username : $a->name;
		$bname = empty($b->name) ? $b->username : $b->name;
		return strtolower($aname) > strtolower($bname);
	}
	
	/**
	 * Print a form to select users.
	 *
	 * @param bool $all Whether to show users who are employees too.
	 * @return module The form's module.
	 */
	public function user_select_form($all = false) {
		global $pines;
		$pines->page->override = true;

		$module = new module('com_customer', 'forms/users');
		if (!$all) {
			$module->users = $pines->entity_manager->get_entities(
				array('class' => user),
				array('&',
					'tag' => array('com_user', 'user')
				),
				array('!&',
					'tag' => array('com_customer', 'customer')
				)
			);
		} else {
			$module->users = $pines->entity_manager->get_entities(
				array('class' => user),
				array('&',
					'tag' => array('com_user', 'user')
				)
			);
		}
		usort($module->users, array($this, 'sort_users'));
		$pines->page->override_doc($module->render());
		return $module;
	}
}

?>