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
     * Delete a customer.
     *
     * @param int $id The GUID of the customer.
     * @return bool True on success, false on failure.
     */
	function delete_customer($id) {
		if ( $entity = $this->get_customer($id) ) {
			$entity->delete();
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
			$entity->delete();
            pines_log("Deleted manufacturer $entity->name.", 'notice');
			return true;
		} else {
			return false;
		}
	}

    /**
     * Delete a tax/free.
     *
     * @param int $id The GUID of the tax/fee.
     * @return bool True on success, false on failure.
     */
	function delete_tax_free($id) {
		if ( $entity = $this->get_tax_fee($id) ) {
			$entity->delete();
            pines_log("Deleted tax / free $entity->name.", 'notice');
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
			$entity->delete();
            pines_log("Deleted vendor $entity->name.", 'notice');
			return true;
		} else {
			return false;
		}
	}

    /**
     * Gets a customer by GUID.
     *
     * @param int $id The customer's GUID.
     * @return entity|null The customer if it exists, null if it doesn't.
     */
    function get_customer($id) {
        global $config;
        $entity = $config->entity_manager->get_entity($id);
        if (is_null($entity) || !$entity->has_tag('com_sales', 'customer'))
            $entity = null;
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
        $entity = $config->entity_manager->get_entity($id);
        if (is_null($entity) || !$entity->has_tag('com_sales', 'manufacturer'))
            $entity = null;
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
        $entity = $config->entity_manager->get_entity($id);
        if (is_null($entity) || !$entity->has_tag('com_sales', 'product'))
            $entity = null;
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
        $entity = $config->entity_manager->get_entity($id);
        if (is_null($entity) || !$entity->has_tag('com_sales', 'tax_fee'))
            $entity = null;
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
        $entity = $config->entity_manager->get_entity($id);
        if (is_null($entity) || !$entity->has_tag('com_sales', 'vendor'))
            $entity = null;
        return $entity;
    }

    /**
     * Creates and attaches a module which lists customers.
     */
	function list_customers() {
		global $config;

		$pgrid = new module('system', 'pgrid.default', 'content');
        $pgrid->icons = true;

		$module = new module('com_sales', 'list_customers', 'content');
		$module->title = "Customers";
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

		$pgrid = new module('system', 'pgrid.default', 'content');
        $pgrid->icons = true;

		$module = new module('com_sales', 'list_manufacturers', 'content');
		$module->title = "Manufacturers";
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
     * Creates and attaches a module which lists taxes/fees.
     */
	function list_tax_fees() {
		global $config;

		$pgrid = new module('system', 'pgrid.default', 'content');
        $pgrid->icons = true;

		$module = new module('com_sales', 'list_tax_fees', 'content');
		$module->title = "Taxes/Fees";
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
     * Creates and attaches a module which lists vendors.
     */
	function list_vendors() {
		global $config;

		$pgrid = new module('system', 'pgrid.default', 'content');
        $pgrid->icons = true;

		$module = new module('com_sales', 'list_vendors', 'content');
		$module->title = "Vendors";
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
     * Creates and attaches a module containing a form for editing a customer.
     *
     * If $id is null, or not given, a blank form will be provided.
     *
     * @param string $heading The heading for the form.
     * @param string $new_option The option to which the form will submit.
     * @param string $new_action The action to which the form will submit.
     * @param int $id The GUID of the customer to edit.
     */
	function print_customer_form($heading, $new_option, $new_action, $id = NULL) {
		global $config;
		$module = new module('com_sales', 'form_customer', 'content');
        $module->title = $heading;
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
        $module->id = $id;
	}

    /**
     * Creates and attaches a module containing a form for editing a manufacturer.
     *
     * If $id is null, or not given, a blank form will be provided.
     *
     * @param string $heading The heading for the form.
     * @param string $new_option The option to which the form will submit.
     * @param string $new_action The action to which the form will submit.
     * @param int $id The GUID of the manufacturer to edit.
     */
	function print_manufacturer_form($heading, $new_option, $new_action, $id = NULL) {
		global $config;
		$module = new module('com_sales', 'form_manufacturer', 'content');
        $module->title = $heading;
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
        $module->id = $id;
	}

    /**
     * Creates and attaches a module containing a form for editing a product.
     *
     * If $id is null, or not given, a blank form will be provided.
     *
     * @param string $heading The heading for the form.
     * @param string $new_option The option to which the form will submit.
     * @param string $new_action The action to which the form will submit.
     * @param int $id The GUID of the product to edit.
     */
	function print_product_form($heading, $new_option, $new_action, $id = NULL) {
		global $config;
        $config->editor->load();
		$module = new module('system', 'tag.editor', 'content');
		$module = new module('com_sales', 'form_product', 'content');
        $module->title = $heading;
		if ( is_null($id) ) {
			$module->entity = new entity;
		} else {
            $module->entity = $this->get_product($id);
            if (is_null($module->entity)) {
                display_error('Requested product id is not accessible.');
                $module->detach();
                return;
            }
		}
        $module->manufacturers = $config->entity_manager->get_entities_by_tags('com_sales', 'manufacturer');
        if (!is_array($module->manufacturers)) {
            $module->manufacturers = array();
        }
        $module->tax_fees = $config->entity_manager->get_entities_by_tags('com_sales', 'tax_fee');
        if (!is_array($module->tax_fees)) {
            $module->tax_fees = array();
        }

        $module->new_option = $new_option;
        $module->new_action = $new_action;
        $module->id = $id;
	}

    /**
     * Creates and attaches a module containing a form for editing a tax/fee.
     *
     * If $id is null, or not given, a blank form will be provided.
     *
     * @param string $heading The heading for the form.
     * @param string $new_option The option to which the form will submit.
     * @param string $new_action The action to which the form will submit.
     * @param int $id The GUID of the tax/fee to edit.
     */
	function print_tax_fee_form($heading, $new_option, $new_action, $id = NULL) {
		global $config;
		$module = new module('com_sales', 'form_tax_fee', 'content');
        $module->title = $heading;
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
        $module->group_array = $config->user_manager->get_group_array();
        $module->new_option = $new_option;
        $module->new_action = $new_action;
        $module->id = $id;
	}

    /**
     * Creates and attaches a module containing a form for editing a vendor.
     *
     * If $id is null, or not given, a blank form will be provided.
     *
     * @param string $heading The heading for the form.
     * @param string $new_option The option to which the form will submit.
     * @param string $new_action The action to which the form will submit.
     * @param int $id The GUID of the vendor to edit.
     */
	function print_vendor_form($heading, $new_option, $new_action, $id = NULL) {
		global $config;
		$module = new module('com_sales', 'form_vendor', 'content');
        $module->title = $heading;
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
        $module->id = $id;
	}
}

?>