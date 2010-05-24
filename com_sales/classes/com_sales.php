<?php
/**
 * com_sales class.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * com_sales main class.
 *
 * Manage sales, products, manufacturers, vendors, etc.
 *
 * @package Pines
 * @subpackage com_sales
 */
class com_sales extends component {
	/**
	 * Calls the first payment process which matches the given arguments.
	 *
	 * @param array $arguments The arguments to pass to appropriate callbacks.
	 * @return bool True on success, false on failure.
	 * @todo Finish calling this in all appropriate places.
	 */
	public function call_payment_process($arguments = array()) {
		global $pines;
		if (!is_array($arguments))
			return false;
		if (empty($arguments['action']))
			return false;
		if ($arguments['action'] != 'request' && !is_object($arguments['sale']))
			return false;
		foreach ($pines->config->com_sales->processing_types as $cur_type) {
			if ($arguments['name'] != $cur_type['name'])
				continue;
			if (!is_callable($cur_type['callback']))
				continue;
			call_user_func_array($cur_type['callback'], array($arguments));
			return true;
		}
	}

	/**
	 * Calls any product actions which match the given arguments.
	 *
	 * @param array $arguments The arguments to pass to appropriate callbacks.
	 * @param int $times How many times to call the callback.
	 * @return bool True on success, false on failure.
	 * @todo Finish calling this in all appropriate places.
	 */
	public function call_product_actions($arguments = array(), $times = 1) {
		global $pines;
		if (!is_array($arguments))
			return false;
		if (empty($arguments['type']))
			return false;
		if (!is_object($arguments['product']))
			return false;
		// If the product has no actions associated with it, don't bother going through the actions.
		if (!is_array($arguments['product']->actions) || empty($arguments['product']->actions))
			return true;
		foreach ($pines->config->com_sales->product_actions as $cur_action) {
			if (is_array($cur_action['type'])) {
				if (!in_array($arguments['type'], $cur_action['type']))
					continue;
			} else {
				if ($arguments['type'] != $cur_action['type'])
					continue;
			}
			if (!in_array($cur_action['name'], $arguments['product']->actions))
				continue;
			if (!is_callable($cur_action['callback']))
				continue;
			$arguments['name'] = $cur_action['name'];
			for ($i = 0; $i < (int) $times; $i++)
				call_user_func_array($cur_action['callback'], array($arguments));
		}
		return true;
	}

	/**
	 * Gets a product by its code.
	 *
	 * The first code checked is the product's SKU. If the product is found, it
	 * is returned, and searching ends. If not, each product's additional
	 * barcodes are checked until a match is found. If no product is found, null
	 * is returned.
	 *
	 * @param int $code The product's code.
	 * @return entity|null The product if it is found, null if it isn't.
	 */
	public function get_product_by_code($code) {
		global $pines;
		$entity = $pines->entity_manager->get_entity(array('data' => array('sku' => $code), 'tags' => array('com_sales', 'product'), 'class' => com_sales_product));
		if (isset($entity))
			return $entity;
		
		$entities = $pines->entity_manager->get_entities(array('tags' => array('com_sales', 'product'), 'class' => com_sales_product));
		if (!is_array($entities))
			return null;
		foreach($entities as $cur_entity) {
			if (!is_array($cur_entity->additional_barcodes))
				continue;
			if (in_array($code, $cur_entity->additional_barcodes))
				return $cur_entity;
		}
		return null;
	}

	/**
	 * Inform the user of anything important.
	 *
	 * @param string $title The title or category of the message.
	 * @param string $header The header title for the message.
	 * @param string $note The content of the message.
	 * @param string $link The url to send the user to upon clicking the header.
	 * @return module The notifcation's module.
	 */
	public function inform($title, $header, $note, $link = null) {
		global $pines;
		$module = new module('com_sales', 'show_note', 'right');
		$module->title = $title;
		$module->header = $header;
		$module->message = $note;
		if (isset($link))
			$module->link = $link;
		return $module;
	}

	/**
	 * Creates and attaches a module which lists cash counts.
	 * @param int $start_date The start date of cash counts to show.
	 * @param int $end_date The end date of cash counts to show.
	 * @param group $location The location to show cash counts for.
	 */
	public function list_cashcounts($start_date = null, $end_date = null, $location = null) {
		global $pines;

		$form = new module('com_sales', 'list_cashcounts_form', 'left');
		$module = new module('com_sales', 'list_cashcounts', 'content');

		if (!isset($start_date))
			$start_date = strtotime('-1 week 00:00');
		if (!isset($end_date))
			$end_date = strtotime('23:59');
		if (!isset($location))
			$location = $_SESSION['user']->group;
		$module->counts = $pines->entity_manager->get_entities(array('gte' => array('p_cdate' => (int) $start_date), 'lte' => array('p_cdate' => (int) $end_date), 'ref' => array('group' => $location), 'tags' => array('com_sales', 'cashcount'), 'class' => com_sales_cashcount));
		$form->start_date = $start_date;
		$form->end_date = $end_date;
		$form->location = $location->guid;

		// Remind the user to do a cash count if one is assigned to their location.
		if ($_SESSION['user']) {
			$_SESSION['user']->refresh();
			if ($_SESSION['user']->group->com_sales_task_cashcount)
				$this->inform('Assignment', 'Cash Drawer Count', 'Please perform a count of the cash in your location\'s drawer. Corporate is awaiting a cash count submission.', pines_url('com_sales', 'editcashcount'));
			if ($_SESSION['user']->group->com_sales_task_cashcount_audit)
				$this->inform('Assignment', 'Cash Drawer Audit', 'Please perform an audit of the cash in your location\'s drawer. Corporate is awaiting a cash drawer audit submission.', pines_url('com_sales', 'auditcashcount'));
			if ($_SESSION['user']->group->com_sales_task_cashcount_deposit)
				$this->inform('Assignment', 'Cash Drawer Deposit', 'Please perform a deposit from the skimmed cash in your location. Corporate is awaiting a cash deposit submission.', pines_url('com_sales', 'depositcashcount'));
			if ($_SESSION['user']->group->com_sales_task_cashcount_skim)
				$this->inform('Assignment', 'Cash Drawer Skim', 'Please perform a skim from the cash in your location\'s drawer. Corporate is awaiting a cash skim submission.', pines_url('com_sales', 'skimcashcount'));
		}

		if ( empty($module->counts) )
			pines_notice('No cash counts found.');
	}

	/**
	 * Creates and attaches a module which lists categories.
	 * @param int $start_date The start date of categories to show.
	 * @param int $end_date The end date of categories to show.
	 * @param group $location The location to show categories for.
	 */
	public function list_categories() {
		global $pines;

		$module = new module('com_sales', 'list_categories', 'content');

		$module->categories = $pines->entity_manager->get_entities(array('tags' => array('com_sales', 'category'), 'class' => com_sales_category));

		if ( empty($module->categories) )
			pines_notice('No categories found.');
	}
	
	/**
	 * Creates and attaches a module which lists countsheets.
	 */
	public function list_countsheets() {
		global $pines;

		$module = new module('com_sales', 'list_countsheets', 'content');

		$module->countsheets = $pines->entity_manager->get_entities(array('tags' => array('com_sales', 'countsheet'), 'class' => com_sales_countsheet));

		// Remind the user to do a countsheet if one is assigned to their location.
		if ($_SESSION['user']) {
			$_SESSION['user']->refresh();
			if ($_SESSION['user']->group->com_sales_task_countsheet)
				$this->inform('Reminder', '<a href="'.pines_url('com_sales', 'editcountsheet').'">Inventory Countsheet &raquo;</a>', 'Please fill out a countsheet for your location when you are not busy. Corporate is awaiting the submission of an inventory count.');
		}
	
		if ( empty($module->countsheets) )
			pines_notice('There are no countsheets.');
	}

	/**
	 * Creates and attaches a module which lists manufacturers.
	 */
	public function list_manufacturers() {
		global $pines;

		$module = new module('com_sales', 'list_manufacturers', 'content');

		$module->manufacturers = $pines->entity_manager->get_entities(array('tags' => array('com_sales', 'manufacturer'), 'class' => com_sales_manufacturer));

		if ( empty($module->manufacturers) )
			pines_notice('There are no manufacturers.');
	}

	/**
	 * Creates and attaches a module which lists payment types.
	 */
	public function list_payment_types() {
		global $pines;

		$module = new module('com_sales', 'list_payment_types', 'content');

		$module->payment_types = $pines->entity_manager->get_entities(array('tags' => array('com_sales', 'payment_type'), 'class' => com_sales_payment_type));

		if ( empty($module->payment_types) )
			pines_notice('There are no payment types.');
	}

	/**
	 * Creates and attaches a module which lists pos.
	 */
	public function list_pos() {
		global $pines;

		$module = new module('com_sales', 'list_pos', 'content');

		$module->pos = $pines->entity_manager->get_entities(array('tags' => array('com_sales', 'po'), 'class' => com_sales_po));

		if ( empty($module->pos) ) {
			pines_notice('There are no POs.');
			return;
		}
		
		// Check the purchase orders to see if any have not been received on time.
		$errors = array();
		foreach ($module->pos as $po) {
			if ($po->eta < time() && empty($po->received))
				$errors[] = "#<strong>{$po->po_number}</strong> was not received on time.";
		}
		if (!empty($errors)) {
			$type = 'Reminder';
			$head = 'Purchase Orders';
			$this->inform($type, $head, implode("\n", $errors));
		}
	}

	/**
	 * Creates and attaches a module which lists products.
	 */
	public function list_products() {
		global $pines;

		$module = new module('com_sales', 'list_products', 'content');

		$module->products = $pines->entity_manager->get_entities(array('tags' => array('com_sales', 'product'), 'class' => com_sales_product));

		if ( empty($module->products) )
			pines_notice('There are no products.');
	}

	/**
	 * Creates and attaches a module which lists sales.
	 * @param int $start_date The start date of sales to show.
	 * @param int $end_date The end date of sales to show.
	 */
	public function list_sales($start_date = null, $end_date = null) {
		global $pines;

		$module = new module('com_sales', 'list_sales', 'content');

		$options = array('tags' => array('com_sales', 'sale'), 'class' => com_sales_sale);
		if (isset($start_date))
			$options['gte'] = array('p_cdate' => (int) $start_date);
		if (isset($end_date))
			$options['lte'] = array('p_cdate' => (int) $end_date);
		$module->sales = $pines->entity_manager->get_entities($options);
		$module->start_date = $start_date;
		$module->end_date = $end_date;

		if ( empty($module->sales) )
			pines_notice('No sales found.');
	}

	/**
	 * Creates and attaches a module which lists shippers.
	 */
	public function list_shippers() {
		global $pines;

		$module = new module('com_sales', 'list_shippers', 'content');

		$module->shippers = $pines->entity_manager->get_entities(array('tags' => array('com_sales', 'shipper'), 'class' => com_sales_shipper));

		if ( empty($module->shippers) )
			pines_notice('There are no shippers.');
	}

	/**
	 * Creates and attaches a module which lists stock.
	 *
	 * @param bool $all Whether to show items that are no longer physically in inventory.
	 */
	public function list_stock($all = false) {
		global $pines;

		$module = new module('com_sales', 'list_stock', 'content');

		$module->stock = $pines->entity_manager->get_entities(array('tags' => array('com_sales', 'stock'), 'class' => com_sales_stock));
		$module->all = $all;

		if ( empty($module->stock) )
			pines_notice('There is nothing in stock at your location.');
	}

	/**
	 * Creates and attaches a module which lists taxes/fees.
	 */
	public function list_tax_fees() {
		global $pines;

		$module = new module('com_sales', 'list_tax_fees', 'content');

		$module->tax_fees = $pines->entity_manager->get_entities(array('tags' => array('com_sales', 'tax_fee'), 'class' => com_sales_tax_fee));

		if ( empty($module->tax_fees) )
			pines_notice('There are no taxes/fees.');
	}

	/**
	 * Creates and attaches a module which lists transfers.
	 */
	public function list_transfers() {
		global $pines;

		$module = new module('com_sales', 'list_transfers', 'content');

		$module->transfers = $pines->entity_manager->get_entities(array('tags' => array('com_sales', 'transfer'), 'class' => com_sales_transfer));

		if ( empty($module->transfers) )
			pines_notice('There are no transfers.');
	}

	/**
	 * Creates and attaches a module which lists vendors.
	 */
	public function list_vendors() {
		global $pines;

		$module = new module('com_sales', 'list_vendors', 'content');

		$module->vendors = $pines->entity_manager->get_entities(array('tags' => array('com_sales', 'vendor'), 'class' => com_sales_vendor));

		if ( empty($module->vendors) )
			pines_notice('There are no vendors.');
	}

	/**
	 * Process an instant approval payment.
	 *
	 * @param array $args The argument array.
	 */
	public function payment_instant($args) {
		switch ($args['action']) {
			case 'approve':
				$args['payment']['status'] = 'approved';
				break;
			case 'tender':
				$args['payment']['status'] = 'tendered';
				$args['payment']['label'] = $args['payment']['entity']->name;
				break;
			case 'change':
				$args['sale']->change_given = true;
				break;
		}
	}

	/**
	 * Process a manager approval payment.
	 *
	 * @param array $args The argument array.
	 */
	public function payment_manager($args) {
		global $pines;
		switch ($args['action']) {
			case 'request':
				$module = new module('com_sales', 'payment_form_manager');
				$pines->page->override_doc($module->render());
				break;
			case 'approve':
				if (gatekeeper('com_sales/manager')) {
					unset($args['payment']['data']['username']);
					unset($args['payment']['data']['password']);
					$args['payment']['status'] = 'approved';
				} else {
					if ($id = $pines->user_manager->authenticate($args['payment']['data']['username'], $args['payment']['data']['password'])) {
						$user = user::factory($id);
						$args['payment']['status'] = gatekeeper('com_sales/manager', $user) ? 'approved' : 'manager_approval_needed';
					} else {
						$args['payment']['status'] = 'manager_approval_needed';
					}
					unset($args['payment']['data']['username']);
					unset($args['payment']['data']['password']);
				}
				break;
			case 'tender':
				$args['payment']['status'] = 'tendered';
				$args['payment']['label'] = $args['payment']['entity']->name;
				break;
			case 'change':
				$args['sale']->change_given = true;
				break;
		}
	}

	/**
	 * Creates and attaches a module containing a form for receiving inventory.
	 *
	 * @return module|null The new module on success, nothing on failure.
	 */
	public function print_receive_form() {
		global $pines;
		$module = new module('com_sales', 'form_receive', 'content');

		return $module;
	}

	/**
	 * Creates and attaches a module containing a sales total page.
	 *
	 * @return module|null The new module on success, nothing on failure.
	 */
	public function print_sales_total() {
		global $pines;
		$module = new module('com_sales', 'total_sales', 'content');
		$module->locations = $pines->user_manager->get_group_array();
		$module->show_all = gatekeeper('com_sales/totalothersales');

		return $module;
	}

	/**
	 * Use gaussian rounding to round a number to a certain decimal point.
	 *
	 * @param float $value The number to round.
	 * @param int $decimal The number of decimal points.
	 * @param bool $string Whether to return a formatted string, instead of a float.
	 * @return float|string Float if $string is false, formatted string otherwise.
	 */
	public function round($value, $decimal, $string = false) {
		$rnd = pow(10, $decimal);
		$mult = $value * $rnd;
		$value = $this->gaussian_round($mult);
		$value /= $rnd;
		if ($string)
			$value = number_format($value, $decimal, '.', '');
		return ($value);
	}

	/**
	 * Round a number to the nearest integer value using gaussian rounding.
	 * 
	 * @param float $value The number to round.
	 * @return float The rounded number.
	 */
	public function gaussian_round($value) {
		$absolute = abs($value);
		$sign     = ($value == 0 ? 0 : ($value < 0 ? -1 : 1));
		$floored  = floor($absolute);
		if ($absolute - $floored != 0.5) {
			return round($absolute) * $sign;
		}
		if ($floored % 2 == 1) {
			// Closest even is up.
			return ceil($absolute) * $sign;
		}
		// Closest even is down.
		return $floored * $sign;
	}
}

?>