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
	function call_payment_process($arguments = array()) {
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
	function call_product_actions($arguments = array(), $times = 1) {
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
	 * Transform a category array into a JSON-ready structure.
	 *
	 * @param array $category_array The array of categories.
	 * @return array A structured array.
	 */
	function category_json_struct($category_array) {
		$struct = array();
		if (!is_array($category_array))
			return $struct;
		foreach ($category_array as $cur_category) {
			if (is_null($cur_category->parent)) {
				$struct[] = array(
					'attributes' => array(
						'id' => $cur_category->guid
					),
					'data' => $cur_category->name,
					'children' => $this->category_json_struct_children($cur_category->guid, $category_array)
				);
			}
		}
		return $struct;
	}

	/**
	 * Parse the children of a category into a JSON-ready structure.
	 *
	 * @param int $guid The GUID of the parent.
	 * @param array $category_array The array of categories.
	 * @access private
	 * @return array|null A structured array, or null if category has no children.
	 */
	protected function category_json_struct_children($guid, $category_array) {
		$struct = array();
		if (!is_array($category_array))
			return null;
		foreach ($category_array as $cur_category) {
			if ($cur_category->parent == $guid || $cur_category->parent->guid == $guid) {
				$struct[] = (object) array(
					'attributes' => (object) array(
						'id' => $cur_category->guid
					),
					'data' => $cur_category->name,
					'children' => $this->category_json_struct_children($cur_category->guid, $category_array)
				);
			}
		}
		if (empty($struct))
			return null;
		return $struct;
	}

	/**
	 * Delete a category recursively.
	 *
	 * @param entity $category The category.
	 * @return bool True on success, false on failure.
	 */
	function delete_category_recursive($category) {
		global $pines;
		$children = $pines->entity_manager->get_entities(array('data' => array('parent' => $category->guid)));
		if (is_array($children)) {
			foreach ($children as $cur_child) {
				if (!$this->delete_category_recursive($cur_child))
					return false;
			}
		}
		if ($category->has_tag('com_sales', 'category')) {
			return $category->delete();
		} else {
			return false;
		}
	}

	/**
	 * Gets a category by GUID.
	 *
	 * @param int $id The category's GUID.
	 * @return entity|null The category if it exists, null if it doesn't.
	 */
	function get_category($id) {
		global $pines;
		$entity = $pines->entity_manager->get_entity(array('guid' => $id, 'tags' => array('com_sales', 'category')));
		return $entity;
	}

	/**
	 * Get an array of category entities.
	 *
	 * @return array The array of entities.
	 */
	function get_category_array() {
		global $pines;
		$entities = $pines->entity_manager->get_entities(array('tags' => array('com_sales', 'category')));
		if (!is_array($entities)) {
			$entities = array();
		}
		return $entities;
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
	function get_product_by_code($code) {
		global $pines;
		$entity = $pines->entity_manager->get_entity(array('data' => array('sku' => $code), 'tags' => array('com_sales', 'product'), 'class' => com_sales_product));
		if (!is_null($entity))
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
	 * Get an array of categories' GUIDs a product belongs to.
	 *
	 * @param entity $product The product.
	 * @return array An array of GUIDs.
	 */
	function get_product_category_guid_array($product) {
		if (!is_object($product))
			return array();
		$categories = $this->get_product_category_array($product);
		foreach ($categories as &$cur_cat) {
			$cur_cat = $cur_cat->guid;
		}
		return $categories;
	}

	/**
	 * Get an array of categories a product belongs to.
	 *
	 * @param entity $product The product.
	 * @return array An array of GUIDs.
	 */
	function get_product_category_array($product) {
		if (!is_object($product))
			return array();
		$categories = $this->get_category_array();
		foreach ($categories as $key => $cur_cat) {
			if (!is_array($cur_cat->products) || !in_array($product->guid, $cur_cat->products)) {
				unset($categories[$key]);
			}
		}
		return $categories;
	}

	/**
	 * Inform the user of anything important.
	 *
	 * @param string $title The title or category of the message.
	 * @param string $header The header title for the message.
	 * @param string $note The content of the message.
	 * @return module The notifcation's module.
	 */
	public function inform($title, $header, $note) {
		global $pines;
		$module = new module('com_sales', 'show_note', 'left');
		$module->title = $title;
		$module->header = $header;
		$module->message = $note;
		return $module;
	}

	/**
	 * Creates and attaches a module which lists countsheets.
	 */
	function list_countsheets() {
		global $pines;

		$pines->com_pgrid->load();
		
		$jstree = new module('system', 'jstree', 'head');
		$module = new module('com_sales', 'list_countsheets', 'content');
		if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
			$module->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_sales/list_countsheets'];

		$module->countsheets = $pines->entity_manager->get_entities(array('tags' => array('com_sales', 'countsheet'), 'class' => com_sales_countsheet));

		if ($_SESSION['user']) {
			$_SESSION['user']->refresh();
			if ($_SESSION['user']->group->com_sales_task_countsheet)
				$this->inform('Reminder', 'Inventory Countsheet', 'Please fill out a countsheet for your location when you are not busy. Corporate is awaiting the submission of an inventory count.');
		}
	
		if ( empty($module->countsheets) ) {
			pines_notice('There are no countsheets.');
			return;
		}
	}

	/**
	 * Creates and attaches a module which lists manufacturers.
	 */
	function list_manufacturers() {
		global $pines;

		$pines->com_pgrid->load();

		$module = new module('com_sales', 'list_manufacturers', 'content');
		if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
			$module->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_sales/list_manufacturers'];

		$module->manufacturers = $pines->entity_manager->get_entities(array('tags' => array('com_sales', 'manufacturer'), 'class' => com_sales_manufacturer));

		if ( empty($module->manufacturers) ) {
			//$module->detach();
			pines_notice('There are no manufacturers.');
		}
	}

	/**
	 * Creates and attaches a module which lists payment types.
	 */
	function list_payment_types() {
		global $pines;

		$pines->com_pgrid->load();

		$module = new module('com_sales', 'list_payment_types', 'content');
		if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
			$module->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_sales/list_payment_types'];

		$module->payment_types = $pines->entity_manager->get_entities(array('tags' => array('com_sales', 'payment_type'), 'class' => com_sales_payment_type));

		if ( empty($module->payment_types) ) {
			//$module->detach();
			pines_notice('There are no payment types.');
		}
	}

	/**
	 * Creates and attaches a module which lists pos.
	 */
	function list_pos() {
		global $pines;

		$pines->com_pgrid->load();

		$module = new module('com_sales', 'list_pos', 'content');
		if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
			$module->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_sales/list_pos'];

		$module->pos = $pines->entity_manager->get_entities(array('tags' => array('com_sales', 'po'), 'class' => com_sales_po));

		if ( empty($module->pos) ) {
			//$module->detach();
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
	function list_products() {
		global $pines;

		$pines->com_pgrid->load();

		$module = new module('com_sales', 'list_products', 'content');
		if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
			$module->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_sales/list_products'];

		$module->products = $pines->entity_manager->get_entities(array('tags' => array('com_sales', 'product'), 'class' => com_sales_product));

		if ( empty($module->products) ) {
			//$module->detach();
			pines_notice('There are no products.');
		}
	}

	/**
	 * Creates and attaches a module which lists sales.
	 * @param int $start_date The start date of sales to show.
	 * @param int $end_date The end date of sales to show.
	 */
	function list_sales($start_date = null, $end_date = null) {
		global $pines;

		$pines->com_pgrid->load();

		$module = new module('com_sales', 'list_sales', 'content');
		if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
			$module->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_sales/list_sales'];

		$options = array('tags' => array('com_sales', 'sale'), 'class' => com_sales_sale);
		if (isset($start_date))
			$options['gte'] = array('p_cdate' => (int) $start_date);
		if (isset($end_date))
			$options['lte'] = array('p_cdate' => (int) $end_date);
		$module->sales = $pines->entity_manager->get_entities($options);
		$module->start_date = $start_date;
		$module->end_date = $end_date;

		if ( empty($module->sales) ) {
			//$module->detach();
			pines_notice('No sales found.');
		}
	}

	/**
	 * Creates and attaches a module which lists shippers.
	 */
	function list_shippers() {
		global $pines;

		$pines->com_pgrid->load();

		$module = new module('com_sales', 'list_shippers', 'content');
		if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
			$module->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_sales/list_shippers'];

		$module->shippers = $pines->entity_manager->get_entities(array('tags' => array('com_sales', 'shipper'), 'class' => com_sales_shipper));

		if ( empty($module->shippers) ) {
			//$module->detach();
			pines_notice('There are no shippers.');
		}
	}

	/**
	 * Creates and attaches a module which lists stock.
	 *
	 * @param bool $all Whether to show items that are no longer physically in inventory.
	 */
	function list_stock($all = false) {
		global $pines;

		$pines->com_pgrid->load();

		$module = new module('com_sales', 'list_stock', 'content');
		if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
			$module->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_sales/list_stock'];

		$module->stock = $pines->entity_manager->get_entities(array('tags' => array('com_sales', 'stock'), 'class' => com_sales_stock));
		$module->all = $all;

		if ( empty($module->stock) ) {
			//$module->detach();
			pines_notice('There is nothing in stock at your location.');
		}
	}

	/**
	 * Creates and attaches a module which lists taxes/fees.
	 */
	function list_tax_fees() {
		global $pines;

		$pines->com_pgrid->load();

		$module = new module('com_sales', 'list_tax_fees', 'content');
		if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
			$module->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_sales/list_tax_fees'];

		$module->tax_fees = $pines->entity_manager->get_entities(array('tags' => array('com_sales', 'tax_fee'), 'class' => com_sales_tax_fee));

		if ( empty($module->tax_fees) ) {
			//$module->detach();
			pines_notice('There are no taxes/fees.');
		}
	}

	/**
	 * Creates and attaches a module which lists transfers.
	 */
	function list_transfers() {
		global $pines;

		$pines->com_pgrid->load();

		$module = new module('com_sales', 'list_transfers', 'content');
		if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
			$module->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_sales/list_transfers'];

		$module->transfers = $pines->entity_manager->get_entities(array('tags' => array('com_sales', 'transfer'), 'class' => com_sales_transfer));

		if ( empty($module->transfers) ) {
			//$module->detach();
			pines_notice('There are no transfers.');
		}
	}

	/**
	 * Creates and attaches a module which lists vendors.
	 */
	function list_vendors() {
		global $pines;

		$pines->com_pgrid->load();

		$module = new module('com_sales', 'list_vendors', 'content');
		if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
			$module->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_sales/list_vendors'];

		$module->vendors = $pines->entity_manager->get_entities(array('tags' => array('com_sales', 'vendor'), 'class' => com_sales_vendor));

		if ( empty($module->vendors) ) {
			//$module->detach();
			pines_notice('There are no vendors.');
		}
	}

	/**
	 * Create and save a new category.
	 *
	 * @param int $parent_id The category's parent's GUID.
	 * @param string $name The category's name.
	 * @return entity|bool The category on success, false on failure.
	 */
	function new_category($parent_id = null, $name = 'untitled') {
		global $pines;
		$entity = new entity('com_sales', 'category');
		$entity->name = $name;
		if (!is_null($parent_id)) {
			$parent = $pines->entity_manager->get_entity(array('guid' => $parent_id, 'tags' => array('com_sales', 'category')));
			if (!is_null($parent))
				$entity->parent = $parent_id;
		}
		$entity->ac = (object) array('user' => 3, 'group' => 3, 'other' => 3);
		if ($entity->save()) {
			return $entity;
		} else {
			return false;
		}
	}

	/**
	 * Process an instant approval payment.
	 *
	 * @param array $args The argument array.
	 */
	function payment_instant($args) {
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
	function payment_manager($args) {
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
	function print_receive_form() {
		global $pines;
		$pines->com_pgrid->load();
		$module = new module('com_sales', 'form_receive', 'content');

		return $module;
	}

	/**
	 * Creates and attaches a module containing a sales total page.
	 *
	 * @return module|null The new module on success, nothing on failure.
	 */
	function print_sales_total() {
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
	function round($value, $decimal, $string = false) {
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
	function gaussian_round($value) {
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