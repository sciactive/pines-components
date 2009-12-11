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
 * Manage sales, customers, manufacturers, vendors, etc.
 *
 * @package Pines
 * @subpackage com_sales
 */
class com_sales extends component {
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
		global $config;
		$children = $config->entity_manager->get_entities_by_parent($category->guid);
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
	 * Delete a customer.
	 *
	 * @param int $id The GUID of the customer.
	 * @return bool True on success, false on failure.
	 */
	function delete_customer($id) {
		if ( $entity = $this->get_customer($id) ) {
			if ( !$entity->delete() )
				return false;
			pines_log("Deleted customer $entity->name.", 'notice');
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Delete a manufacturer.
	 *
	 * @param int $id The GUID of the manufacturer.
	 * @return bool True on success, false on failure.
	 */
	function delete_manufacturer($id) {
		if ( $entity = $this->get_manufacturer($id) ) {
			if ( !$entity->delete() )
				return false;
			pines_log("Deleted manufacturer $entity->name.", 'notice');
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Delete a payment type.
	 *
	 * @param int $id The GUID of the payment type.
	 * @return bool True on success, false on failure.
	 */
	function delete_payment_type($id) {
		if ( $entity = $this->get_payment_type($id) ) {
			if ( !$entity->delete() )
				return false;
			pines_log("Deleted payment type $entity->name.", 'notice');
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Delete a PO.
	 *
	 * @param int $id The GUID of the PO.
	 * @return bool True on success, false on failure.
	 */
	function delete_po($id) {
		if ( $entity = $this->get_po($id) ) {
			// Don't delete the PO if it has received items.
			if (!empty($entity->received))
				return false;
			if ( !$entity->delete() )
				return false;
			pines_log("Deleted PO $entity->po_number.", 'notice');
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Delete a product.
	 *
	 * @param int $id The GUID of the product.
	 * @return bool True on success, false on failure.
	 */
	function delete_product($id) {
		if ( $entity = $this->get_product($id) ) {
			if ( !$entity->delete() )
				return false;
			pines_log("Deleted product $entity->name.", 'notice');
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Delete a shipper.
	 *
	 * @param int $id The GUID of the shipper.
	 * @return bool True on success, false on failure.
	 */
	function delete_shipper($id) {
		if ( $entity = $this->get_shipper($id) ) {
			if ( !$entity->delete() )
				return false;
			pines_log("Deleted shipper $entity->name.", 'notice');
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Delete a tax/fee.
	 *
	 * @param int $id The GUID of the tax/fee.
	 * @return bool True on success, false on failure.
	 */
	function delete_tax_fee($id) {
		if ( $entity = $this->get_tax_fee($id) ) {
			if ( !$entity->delete() )
				return false;
			pines_log("Deleted tax/fee $entity->name.", 'notice');
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Delete a transfer.
	 *
	 * @param int $id The GUID of the transfer.
	 * @return bool True on success, false on failure.
	 */
	function delete_transfer($id) {
		if ( $entity = $this->get_transfer($id) ) {
			// Don't delete the transfer if it has received items.
			if (!empty($entity->received))
				return false;
			if ( !$entity->delete() )
				return false;
			pines_log("Deleted transfer $entity->guid.", 'notice');
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Delete a vendor.
	 *
	 * @param int $id The GUID of the vendor.
	 * @return bool True on success, false on failure.
	 */
	function delete_vendor($id) {
		if ( $entity = $this->get_vendor($id) ) {
			if ( !$entity->delete() )
				return false;
			pines_log("Deleted vendor $entity->name.", 'notice');
			return true;
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
		global $config;
		$entity = $config->entity_manager->get_entity($id, array('com_sales', 'category'));
		return $entity;
	}

	/**
	 * Get an array of category entities.
	 *
	 * @return array The array of entities.
	 */
	function get_category_array() {
		global $config;
		$entities = $config->entity_manager->get_entities_by_tags('com_sales', 'category');
		if (!is_array($entities)) {
			$entities = array();
		}
		return $entities;
	}

	/**
	 * Gets a customer by GUID.
	 *
	 * @param int $id The customer's GUID.
	 * @return entity|null The customer if it exists, null if it doesn't.
	 */
	function get_customer($id) {
		global $config;
		$entity = $config->entity_manager->get_entity($id, array('com_sales', 'customer'));
		return $entity;
	}

	/**
	 * Gets a manufacturer by GUID.
	 *
	 * @param int $id The manufacturer's GUID.
	 * @return entity|null The manufacturer if it exists, null if it doesn't.
	 */
	function get_manufacturer($id) {
		global $config;
		$entity = $config->entity_manager->get_entity($id, array('com_sales', 'manufacturer'));
		return $entity;
	}

	/**
	 * Gets a payment type by GUID.
	 *
	 * @param int $id The payment type's GUID.
	 * @return entity|null The payment type if it exists, null if it doesn't.
	 */
	function get_payment_type($id) {
		global $config;
		$entity = $config->entity_manager->get_entity($id, array('com_sales', 'payment_type'));
		return $entity;
	}

	/**
	 * Gets a PO by GUID.
	 *
	 * @param int $id The PO's GUID.
	 * @return entity|null The PO if it exists, null if it doesn't.
	 */
	function get_po($id) {
		global $config;
		$entity = $config->entity_manager->get_entity($id, array('com_sales', 'po'));
		return $entity;
	}

	/**
	 * Gets a product by GUID.
	 *
	 * @param int $id The product's GUID.
	 * @return entity|null The product if it exists, null if it doesn't.
	 */
	function get_product($id) {
		global $config;
		$entity = $config->entity_manager->get_entity($id, array('com_sales', 'product'));
		return $entity;
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
		global $config;
		$entities = $config->entity_manager->get_entities_by_data(array('sku' => $code), array('com_sales', 'product'));
		if (!empty($entities)) {
			return $entities[0];
		}
		$entities = $config->entity_manager->get_entities_by_tags('com_sales', 'product');
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
	 * Gets a shipper by GUID.
	 *
	 * @param int $id The shipper's GUID.
	 * @return entity|null The shipper if it exists, null if it doesn't.
	 */
	function get_shipper($id) {
		global $config;
		$entity = $config->entity_manager->get_entity($id, array('com_sales', 'shipper'));
		return $entity;
	}

	/**
	 * Gets a tax/fee by GUID.
	 *
	 * @param int $id The tax/fee's GUID.
	 * @return entity|null The tax/fee if it exists, null if it doesn't.
	 */
	function get_tax_fee($id) {
		global $config;
		$entity = $config->entity_manager->get_entity($id, array('com_sales', 'tax_fee'));
		return $entity;
	}

	/**
	 * Gets a transfer by GUID.
	 *
	 * @param int $id The transfer's GUID.
	 * @return entity|null The transfer if it exists, null if it doesn't.
	 */
	function get_transfer($id) {
		global $config;
		$entity = $config->entity_manager->get_entity($id, array('com_sales', 'transfer'));
		return $entity;
	}

	/**
	 * Gets a vendor by GUID.
	 *
	 * @param int $id The vendor's GUID.
	 * @return entity|null The vendor if it exists, null if it doesn't.
	 */
	function get_vendor($id) {
		global $config;
		$entity = $config->entity_manager->get_entity($id, array('com_sales', 'vendor'));
		return $entity;
	}

	/**
	 * Creates and attaches a module which lists customers.
	 */
	function list_customers() {
		global $config;

		$pgrid = new module('system', 'pgrid.default', 'head');
		$pgrid->icons = true;

		$module = new module('com_sales', 'list_customers', 'content');
		if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
			$module->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_sales/list_customers'];

		$module->customers = $config->entity_manager->get_entities_by_tags('com_sales', 'customer');

		if ( empty($module->customers) ) {
			$pgrid->detach();
			$module->detach();
			display_notice("There are no customers.");
		}
	}

	/**
	 * Creates and attaches a module which lists manufacturers.
	 */
	function list_manufacturers() {
		global $config;

		$pgrid = new module('system', 'pgrid.default', 'head');
		$pgrid->icons = true;

		$module = new module('com_sales', 'list_manufacturers', 'content');
		if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
			$module->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_sales/list_manufacturers'];

		$module->manufacturers = $config->entity_manager->get_entities_by_tags('com_sales', 'manufacturer');

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
		global $config;

		$pgrid = new module('system', 'pgrid.default', 'head');
		$pgrid->icons = true;

		$module = new module('com_sales', 'list_payment_types', 'content');
		if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
			$module->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_sales/list_payment_types'];

		$module->payment_types = $config->entity_manager->get_entities_by_tags('com_sales', 'payment_type');

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
		global $config;

		$pgrid = new module('system', 'pgrid.default', 'head');
		$pgrid->icons = true;

		$module = new module('com_sales', 'list_pos', 'content');
		if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
			$module->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_sales/list_pos'];

		$module->pos = $config->entity_manager->get_entities_by_tags('com_sales', 'po');

		if ( empty($module->pos) ) {
			$pgrid->detach();
			$module->detach();
			display_notice("There are no POs.");
		}
	}

	/**
	 * Creates and attaches a module which lists products.
	 */
	function list_products() {
		global $config;

		$pgrid = new module('system', 'pgrid.default', 'head');
		$pgrid->icons = true;

		$module = new module('com_sales', 'list_products', 'content');
		if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
			$module->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_sales/list_products'];

		$module->products = $config->entity_manager->get_entities_by_tags('com_sales', 'product');

		if ( empty($module->products) ) {
			$pgrid->detach();
			$module->detach();
			display_notice("There are no products.");
		}
	}

	/**
	 * Creates and attaches a module which lists shippers.
	 */
	function list_shippers() {
		global $config;

		$pgrid = new module('system', 'pgrid.default', 'head');
		$pgrid->icons = true;

		$module = new module('com_sales', 'list_shippers', 'content');
		if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
			$module->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_sales/list_shippers'];

		$module->shippers = $config->entity_manager->get_entities_by_tags('com_sales', 'shipper');

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
		global $config;

		$pgrid = new module('system', 'pgrid.default', 'head');
		$pgrid->icons = true;

		$module = new module('com_sales', 'list_stock', 'content');
		if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
			$module->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_sales/list_stock'];

		$module->stock = $config->entity_manager->get_entities_by_tags('com_sales', 'stock_entry', stock_entry);
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
		global $config;

		$pgrid = new module('system', 'pgrid.default', 'head');
		$pgrid->icons = true;

		$module = new module('com_sales', 'list_tax_fees', 'content');
		if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
			$module->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_sales/list_tax_fees'];

		$module->tax_fees = $config->entity_manager->get_entities_by_tags('com_sales', 'tax_fee');

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
		global $config;

		$pgrid = new module('system', 'pgrid.default', 'head');
		$pgrid->icons = true;

		$module = new module('com_sales', 'list_transfers', 'content');
		if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
			$module->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_sales/list_transfers'];

		$module->transfers = $config->entity_manager->get_entities_by_tags('com_sales', 'transfer');

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
		global $config;

		$pgrid = new module('system', 'pgrid.default', 'head');
		$pgrid->icons = true;

		$module = new module('com_sales', 'list_vendors', 'content');
		if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
			$module->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_sales/list_vendors'];

		$module->vendors = $config->entity_manager->get_entities_by_tags('com_sales', 'vendor');

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
		global $config;
		$entity = new entity('com_sales', 'category');
		$entity->name = $name;
		if (!is_null($parent_id)) {
			$parent = $config->entity_manager->get_entity($parent_id, array('com_sales', 'category'));
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
	 * Creates and attaches a module containing a form for editing a customer.
	 *
	 * If $id is null, or not given, a blank form will be provided.
	 *
	 * @param string $new_option The option to which the form will submit.
	 * @param string $new_action The action to which the form will submit.
	 * @param int $id The GUID of the customer to edit.
	 * @return module|null The new module on success, nothing on failure.
	 */
	function print_customer_form($new_option, $new_action, $id = NULL) {
		global $config;
		$module = new module('com_sales', 'form_customer', 'content');
		if ( is_null($id) ) {
			$module->entity = new entity;
		} else {
			$module->entity = $this->get_customer($id);
			if (is_null($module->entity)) {
				display_error('Requested customer id is not accessible.');
				$module->detach();
				return;
			}
		}
		$module->new_option = $new_option;
		$module->new_action = $new_action;

		return $module;
	}

	/**
	 * Creates and attaches a module containing a form for editing a
	 * manufacturer.
	 *
	 * If $id is null, or not given, a blank form will be provided.
	 *
	 * @param string $new_option The option to which the form will submit.
	 * @param string $new_action The action to which the form will submit.
	 * @param int $id The GUID of the manufacturer to edit.
	 * @return module|null The new module on success, nothing on failure.
	 */
	function print_manufacturer_form($new_option, $new_action, $id = NULL) {
		global $config;
		$module = new module('com_sales', 'form_manufacturer', 'content');
		if ( is_null($id) ) {
			$module->entity = new entity;
		} else {
			$module->entity = $this->get_manufacturer($id);
			if (is_null($module->entity)) {
				display_error('Requested manufacturer id is not accessible.');
				$module->detach();
				return;
			}
		}
		$module->new_option = $new_option;
		$module->new_action = $new_action;

		return $module;
	}

	/**
	 * Creates and attaches a module containing a form for editing a payment type.
	 *
	 * If $id is null, or not given, a blank form will be provided.
	 *
	 * @param string $new_option The option to which the form will submit.
	 * @param string $new_action The action to which the form will submit.
	 * @param int $id The GUID of the payment type to edit.
	 * @return module|null The new module on success, nothing on failure.
	 */
	function print_payment_type_form($new_option, $new_action, $id = NULL) {
		global $config;
		$module = new module('com_sales', 'form_payment_type', 'content');
		if ( is_null($id) ) {
			$module->entity = new entity;
		} else {
			$module->entity = $this->get_payment_type($id);
			if (is_null($module->entity)) {
				display_error('Requested payment type id is not accessible.');
				$module->detach();
				return;
			}
		}
		$module->new_option = $new_option;
		$module->new_action = $new_action;

		return $module;
	}

	/**
	 * Creates and attaches a module containing a form for editing a PO.
	 *
	 * If $id is null, or not given, a blank form will be provided.
	 *
	 * @param string $new_option The option to which the form will submit.
	 * @param string $new_action The action to which the form will submit.
	 * @param int $id The GUID of the po to edit.
	 * @return module|null The new module on success, nothing on failure.
	 */
	function print_po_form($new_option, $new_action, $id = NULL) {
		global $config;
		$pgrid = new module('system', 'pgrid.default', 'head');
		$pgrid->icons = true;
		$module = new module('com_sales', 'form_po', 'content');
		if ( is_null($id) ) {
			$module->entity = new entity;
		} else {
			$module->entity = $this->get_po($id);
			if (is_null($module->entity)) {
				display_error('Requested PO id is not accessible.');
				$jstree->detach();
				$tageditor->detach();
				$module->detach();
				return;
			}
		}
		$module->locations = $config->user_manager->get_group_array();
		$module->shippers = $config->entity_manager->get_entities_by_tags('com_sales', 'shipper');
		if (!is_array($module->shippers)) {
			$module->shippers = array();
		}
		$module->vendors = $config->entity_manager->get_entities_by_tags('com_sales', 'vendor');
		if (!is_array($module->vendors)) {
			$module->vendors = array();
		}
		$module->products = $config->entity_manager->get_entities_by_tags('com_sales', 'product');
		if (!is_array($module->products)) {
			$module->products = array();
		}

		$module->new_option = $new_option;
		$module->new_action = $new_action;

		return $module;
	}

	/**
	 * Creates and attaches a module containing a form for editing a product.
	 *
	 * If $id is null, or not given, a blank form will be provided.
	 *
	 * @param string $new_option The option to which the form will submit.
	 * @param string $new_action The action to which the form will submit.
	 * @param int $id The GUID of the product to edit.
	 * @return module|null The new module on success, nothing on failure.
	 */
	function print_product_form($new_option, $new_action, $id = NULL) {
		global $config;
		$config->editor->load();
		$pgrid = new module('system', 'pgrid.default', 'head');
		$pgrid->icons = true;
		$jstree = new module('system', 'jstree', 'head');
		$ptags = new module('system', 'ptags.default', 'head');
		$module = new module('com_sales', 'form_product', 'content');
		if ( is_null($id) ) {
			$module->entity = new entity;
		} else {
			$module->entity = $this->get_product($id);
			if (is_null($module->entity)) {
				display_error('Requested product id is not accessible.');
				$jstree->detach();
				$ptags->detach();
				$module->detach();
				return;
			}
		}
		$module->manufacturers = $config->entity_manager->get_entities_by_tags('com_sales', 'manufacturer');
		if (!is_array($module->manufacturers)) {
			$module->manufacturers = array();
		}
		$module->vendors = $config->entity_manager->get_entities_by_tags('com_sales', 'vendor');
		if (!is_array($module->vendors)) {
			$module->vendors = array();
		}
		$module->tax_fees = $config->entity_manager->get_entities_by_tags('com_sales', 'tax_fee');
		if (!is_array($module->tax_fees)) {
			$module->tax_fees = array();
		}

		$module->new_option = $new_option;
		$module->new_action = $new_action;

		return $module;
	}

	/**
	 * Creates and attaches a module containing a form for receiving inventory.
	 *
	 * @param string $new_option The option to which the form will submit.
	 * @param string $new_action The action to which the form will submit.
	 * @return module|null The new module on success, nothing on failure.
	 */
	function print_receive_form($new_option, $new_action) {
		global $config;
		$pgrid = new module('system', 'pgrid.default', 'head');
		$pgrid->icons = true;
		$module = new module('com_sales', 'form_receive', 'content');
		$module->new_option = $new_option;
		$module->new_action = $new_action;

		return $module;
	}

	/**
	 * Creates and attaches a module containing a form for editing a
	 * sale.
	 *
	 * If $id is null, or not given, a blank form will be provided.
	 *
	 * @param string $new_option The option to which the form will submit.
	 * @param string $new_action The action to which the form will submit.
	 * @param int $id The GUID of the sale to edit.
	 * @return module|null The new module on success, nothing on failure.
	 */
	function print_sale_form($new_option, $new_action, $id = NULL) {
		global $config;
		$pgrid = new module('system', 'pgrid.default', 'head');
		$pgrid->icons = true;
		$module = new module('com_sales', 'form_sale', 'content');
		if ( is_null($id) ) {
			$module->entity = new entity;
		} else {
			$module->entity = $this->get_sale($id);
			if (is_null($module->entity)) {
				display_error('Requested sale id is not accessible.');
				$module->detach();
				return;
			}
		}
		$module->tax_fees = $config->entity_manager->get_entities_by_tags('com_sales', 'tax_fee');
		if (!is_array($module->tax_fees)) {
			$module->tax_fees = array();
		}
		$module->payment_types = $config->entity_manager->get_entities_by_tags('com_sales', 'payment_type');
		if (!is_array($module->payment_types)) {
			$module->payment_types = array();
		}
		foreach ($module->payment_types as $key => $cur_payment_type) {
			if (!$cur_payment_type->enabled) {
				unset($module->payment_types[$key]);
			}
		}

		$module->new_option = $new_option;
		$module->new_action = $new_action;

		return $module;
	}

	/**
	 * Creates and attaches a module containing a form for editing a
	 * shipper.
	 *
	 * If $id is null, or not given, a blank form will be provided.
	 *
	 * @param string $new_option The option to which the form will submit.
	 * @param string $new_action The action to which the form will submit.
	 * @param int $id The GUID of the shipper to edit.
	 * @return module|null The new module on success, nothing on failure.
	 */
	function print_shipper_form($new_option, $new_action, $id = NULL) {
		global $config;
		$module = new module('com_sales', 'form_shipper', 'content');
		if ( is_null($id) ) {
			$module->entity = new entity;
		} else {
			$module->entity = $this->get_shipper($id);
			if (is_null($module->entity)) {
				display_error('Requested shipper id is not accessible.');
				$module->detach();
				return;
			}
		}
		$module->new_option = $new_option;
		$module->new_action = $new_action;

		return $module;
	}

	/**
	 * Creates and attaches a module containing a form for editing a tax/fee.
	 *
	 * If $id is null, or not given, a blank form will be provided.
	 *
	 * @param string $new_option The option to which the form will submit.
	 * @param string $new_action The action to which the form will submit.
	 * @param int $id The GUID of the tax/fee to edit.
	 * @return module|null The new module on success, nothing on failure.
	 */
	function print_tax_fee_form($new_option, $new_action, $id = NULL) {
		global $config;
		$module = new module('com_sales', 'form_tax_fee', 'content');
		if ( is_null($id) ) {
			$module->entity = new entity;
		} else {
			$module->entity = $this->get_tax_fee($id);
			if (is_null($module->entity)) {
				display_error('Requested tax/fee id is not accessible.');
				$module->detach();
				return;
			}
		}
		$module->locations = $config->user_manager->get_group_array();
		$module->new_option = $new_option;
		$module->new_action = $new_action;

		return $module;
	}

	/**
	 * Creates and attaches a module containing a form for editing a transfer.
	 *
	 * If $id is null, or not given, a blank form will be provided.
	 *
	 * @param string $new_option The option to which the form will submit.
	 * @param string $new_action The action to which the form will submit.
	 * @param int $id The GUID of the transfer to edit.
	 * @return module|null The new module on success, nothing on failure.
	 */
	function print_transfer_form($new_option, $new_action, $id = NULL) {
		global $config;
		$pgrid = new module('system', 'pgrid.default', 'head');
		$pgrid->icons = true;
		$module = new module('com_sales', 'form_transfer', 'content');
		if ( is_null($id) ) {
			$module->entity = new entity;
		} else {
			$module->entity = $this->get_transfer($id);
			if (is_null($module->entity)) {
				display_error('Requested transfer id is not accessible.');
				$jstree->detach();
				$tageditor->detach();
				$module->detach();
				return;
			}
		}
		$module->locations = $config->user_manager->get_group_array();
		$module->shippers = $config->entity_manager->get_entities_by_tags('com_sales', 'shipper');
		if (!is_array($module->shippers)) {
			$module->shippers = array();
		}
		$module->stock = $config->entity_manager->get_entities_by_tags('com_sales', 'stock_entry', stock_entry);
		if (!is_array($module->stock)) {
			$module->stock = array();
		}

		$module->new_option = $new_option;
		$module->new_action = $new_action;

		return $module;
	}

	/**
	 * Creates and attaches a module containing a form for editing a vendor.
	 *
	 * If $id is null, or not given, a blank form will be provided.
	 *
	 * @param string $new_option The option to which the form will submit.
	 * @param string $new_action The action to which the form will submit.
	 * @param int $id The GUID of the vendor to edit.
	 * @return module|null The new module on success, nothing on failure.
	 */
	function print_vendor_form($new_option, $new_action, $id = NULL) {
		global $config;
		$module = new module('com_sales', 'form_vendor', 'content');
		if ( is_null($id) ) {
			$module->entity = new entity;
		} else {
			$module->entity = $this->get_vendor($id);
			if (is_null($module->entity)) {
				display_error('Requested vendor id is not accessible.');
				$module->detach();
				return;
			}
		}
		$module->new_option = $new_option;
		$module->new_action = $new_action;

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
	function round($value, $decimal, $string = true) {
		$rnd = 10 ^ $decimal;
		$mult = $value * $rnd;
		$value = $this->gaussian_round($mult);
		$value /= $rnd;
		if ($string)
			$value = number_format($value, $decimal);
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