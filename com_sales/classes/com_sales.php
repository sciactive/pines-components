<?php
/**
 * com_sales class.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
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
	 * Whether to integrate with com_customer.
	 *
	 * @var bool $com_customer
	 */
	var $com_customer;

	/**
	 * List of payment processing types.
	 *
	 * Payment processing types allow another component to handle the processing
	 * of payments, such as credit card or gift card payments.
	 *
	 * To add a processing type, your code must add a new array with the
	 * following values:
	 *
	 * - "name" - The name of your type. Ex: 'com_giftcard/giftcard'
	 * - "cname" - The canonical name of your action. Ex: 'Gift Card'
	 * - "description" - A description of the action. Ex: 'Deduct the payment from a gift card.'
	 * - "callback" - Callback to your function. Ex: array($pines->run_giftcard, 'process_giftcard')
	 *
	 * The callback will be passed an array which may contain the following
	 * associative entries:
	 *
	 * - "action" - The processing which is being requested.
	 * - "name" - The name of the type being called.
	 * - "payment" - The sale's payment entry. This holds information about the payment.
	 * - "sale" - The sale entity.
	 *
	 * "action" will be one of:
	 *
	 * - "request" - The payment type has been selected.
	 * - "approve" - The sale is being invoiced, and the payment needs to be approved.
	 * - "tender" - The sale is being processed, and the payment needs to be processed.
	 * - "change" - The sale requires change to be given, and this payment type has been selected to give change.
	 * - "return" - The payment is being returned and the funds need to be returned.
	 *
	 * If "action" is "request", the callback can provide a form to collect
	 * information from the user by calling $pines->page->override_doc() with
	 * the HTML of the form. It is recommended to use a module to provide the
	 * form's HTML. Use $module->render() to get the HTML from the module. If
	 * you do not need any information from the user, simply don't do anything.
	 * The form's inputs will be parsed into an array and saved as "data" in the
	 * payment entry.
	 *
	 * If "action" is "approve", the callback needs to set the "status" entry on
	 * the payment array to "approved", "declined", "info_requested", or
	 * "manager_approval_needed".
	 *
	 * If "action" is "tender", the callback can then also set the "status" to
	 * "tendered".
	 *
	 * If "action" is "change", the callback needs to set the "change_given"
	 * variable on the sale object to true or false.
	 *
	 * @var array $processing_types
	 */
	public $processing_types = array();

	/**
	 * List of product actions.
	 *
	 * Product actions are callbacks that can be called when a product is
	 * received, adjusted, sold, or returned.
	 *
	 * To add a product action, your code must add a new array with the
	 * following values:
	 *
	 * - "type" - An array or string of the event(s) the action should be called for. Out of "received", "adjusted", "sold", and "returned".
	 * - "name" - The name of your action. Ex: 'com_gamephear/create_gamephear_account'
	 * - "cname" - The canonical name of your action. Ex: 'Create GamePhear Account'
	 * - "description" - A description of the action. Ex: 'Creates a GamePhear account for the customer.'
	 * - "callback" - Callback to your function. Ex: array($pines->run_gamephear, 'create_account')
	 *
	 * The callback will be passed an array which may contain the following
	 * associative entries:
	 *
	 * - "type" - The type of event that has occurred.
	 * - "name" - The name of the action being called.
	 * - "product" - The product entity.
	 * - "stock_entry" - The stock entry entity.
	 * - "sale" - The sale entity.
	 * - "po" - The PO entity.
	 * - "transfer" - The transfer entity.
	 *
	 * @var array $product_actions
	 */
	public $product_actions = array();

	/**
	 * Check whether com_customer is installed and we should integrate with it.
	 *
	 * Places the result in $this->com_customer.
	 */
	function __construct() {
		global $pines;
		$this->com_customer = $pines->depend->check('component', 'com_customer');
	}

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
		foreach ($this->processing_types as $cur_type) {
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
		if (!is_array($arguments))
			return false;
		if (empty($arguments['type']))
			return false;
		if (!is_object($arguments['product']))
			return false;
		// If the product has no actions associated with it, don't bother going through the actions.
		if (!is_array($arguments['product']->actions) || empty($arguments['product']->actions))
			return true;
		foreach ($this->product_actions as $cur_action) {
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
			if ($cur_category->parent == $guid) {
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
	 * Creates and attaches a module which lists manufacturers.
	 */
	function list_manufacturers() {
		global $pines;

		$pgrid = new module('system', 'pgrid.default', 'head');
		$pgrid->icons = true;

		$module = new module('com_sales', 'list_manufacturers', 'content');
		if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
			$module->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_sales/list_manufacturers'];

		$module->manufacturers = $pines->entity_manager->get_entities(array('tags' => array('com_sales', 'manufacturer'), 'class' => com_sales_manufacturer));

		if ( empty($module->manufacturers) ) {
			$pgrid->detach();
			$module->detach();
			display_notice("There are no manufacturers.");
		}
	}

	/**
	 * Creates and attaches a module which lists payment types.
	 */
	function list_payment_types() {
		global $pines;

		$pgrid = new module('system', 'pgrid.default', 'head');
		$pgrid->icons = true;

		$module = new module('com_sales', 'list_payment_types', 'content');
		if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
			$module->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_sales/list_payment_types'];

		$module->payment_types = $pines->entity_manager->get_entities(array('tags' => array('com_sales', 'payment_type'), 'class' => com_sales_payment_type));

		if ( empty($module->payment_types) ) {
			$pgrid->detach();
			$module->detach();
			display_notice("There are no payment types.");
		}
	}

	/**
	 * Creates and attaches a module which lists pos.
	 */
	function list_pos() {
		global $pines;

		$pgrid = new module('system', 'pgrid.default', 'head');
		$pgrid->icons = true;

		$module = new module('com_sales', 'list_pos', 'content');
		if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
			$module->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_sales/list_pos'];

		$module->pos = $pines->entity_manager->get_entities(array('tags' => array('com_sales', 'po'), 'class' => com_sales_po));

		if ( empty($module->pos) ) {
			$pgrid->detach();
			$module->detach();
			display_notice("There are no POs.");
			return;
		}
		
		// Check the purchase orders to see if any have not been received on time.
		$errors = array();
		foreach ($module->pos as $po) {
			if ($po->eta < time() && empty($po->received))
				$errors[] .= 'PO #'. $po->po_number .' was not recieved on time.';
		}
		if (!empty($errors)) {
			$type = 'Reminder';
			$head = 'Purchase Orders';
			$this->notify($type,$head,$errors);
		}
	}

	/**
	 * Creates and attaches a module which lists products.
	 */
	function list_products() {
		global $pines;

		$pgrid = new module('system', 'pgrid.default', 'head');
		$pgrid->icons = true;

		$module = new module('com_sales', 'list_products', 'content');
		if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
			$module->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_sales/list_products'];

		$module->products = $pines->entity_manager->get_entities(array('tags' => array('com_sales', 'product'), 'class' => com_sales_product));

		if ( empty($module->products) ) {
			$pgrid->detach();
			$module->detach();
			display_notice("There are no products.");
		}
	}

	/**
	 * Creates and attaches a module which lists sales.
	 */
	function list_sales() {
		global $pines;

		$pgrid = new module('system', 'pgrid.default', 'head');
		$pgrid->icons = true;

		$module = new module('com_sales', 'list_sales', 'content');
		if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
			$module->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_sales/list_sales'];

		$module->sales = $pines->entity_manager->get_entities(array('tags' => array('com_sales', 'sale'), 'class' => com_sales_sale));

		if ( empty($module->sales) ) {
			$pgrid->detach();
			$module->detach();
			display_notice("There are no sales.");
		}
	}

	/**
	 * Creates and attaches a module which lists shippers.
	 */
	function list_shippers() {
		global $pines;

		$pgrid = new module('system', 'pgrid.default', 'head');
		$pgrid->icons = true;

		$module = new module('com_sales', 'list_shippers', 'content');
		if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
			$module->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_sales/list_shippers'];

		$module->shippers = $pines->entity_manager->get_entities(array('tags' => array('com_sales', 'shipper'), 'class' => com_sales_shipper));

		if ( empty($module->shippers) ) {
			$pgrid->detach();
			$module->detach();
			display_notice("There are no shippers.");
		}
	}

	/**
	 * Creates and attaches a module which lists stock.
	 *
	 * @param bool $all Whether to show items that are no longer physically in inventory.
	 */
	function list_stock($all = false) {
		global $pines;

		$pgrid = new module('system', 'pgrid.default', 'head');
		$pgrid->icons = true;

		$module = new module('com_sales', 'list_stock', 'content');
		if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
			$module->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_sales/list_stock'];

		$module->stock = $pines->entity_manager->get_entities(array('tags' => array('com_sales', 'stock'), 'class' => com_sales_stock));
		$module->all = $all;

		if ( empty($module->stock) ) {
			$pgrid->detach();
			$module->detach();
			display_notice("There is nothing in stock at your location.");
		}
	}

	/**
	 * Creates and attaches a module which lists taxes/fees.
	 */
	function list_tax_fees() {
		global $pines;

		$pgrid = new module('system', 'pgrid.default', 'head');
		$pgrid->icons = true;

		$module = new module('com_sales', 'list_tax_fees', 'content');
		if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
			$module->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_sales/list_tax_fees'];

		$module->tax_fees = $pines->entity_manager->get_entities(array('tags' => array('com_sales', 'tax_fee'), 'class' => com_sales_tax_fee));

		if ( empty($module->tax_fees) ) {
			$pgrid->detach();
			$module->detach();
			display_notice("There are no taxes/fees.");
		}
	}

	/**
	 * Creates and attaches a module which lists transfers.
	 */
	function list_transfers() {
		global $pines;

		$pgrid = new module('system', 'pgrid.default', 'head');
		$pgrid->icons = true;

		$module = new module('com_sales', 'list_transfers', 'content');
		if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
			$module->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_sales/list_transfers'];

		$module->transfers = $pines->entity_manager->get_entities(array('tags' => array('com_sales', 'transfer'), 'class' => com_sales_transfer));

		if ( empty($module->transfers) ) {
			$pgrid->detach();
			$module->detach();
			display_notice("There are no transfers.");
		}
	}

	/**
	 * Creates and attaches a module which lists vendors.
	 */
	function list_vendors() {
		global $pines;

		$pgrid = new module('system', 'pgrid.default', 'head');
		$pgrid->icons = true;

		$module = new module('com_sales', 'list_vendors', 'content');
		if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
			$module->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_sales/list_vendors'];

		$module->vendors = $pines->entity_manager->get_entities(array('tags' => array('com_sales', 'vendor'), 'class' => com_sales_vendor));

		if ( empty($module->vendors) ) {
			$pgrid->detach();
			$module->detach();
			display_notice("There are no vendors.");
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
		$pgrid = new module('system', 'pgrid.default', 'head');
		$pgrid->icons = true;
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

	/**
	 * Notify the user of anything important.
	 * @return module The notifcation's module.
	 */
	public function notify($title, $header, $note) {
		global $pines;
		$module = new module('com_sales', 'show_note', 'left');
		$module->title = $title;
		$module->header = $header;
		$module->message = $note;
		return $module;
	}
}

?>