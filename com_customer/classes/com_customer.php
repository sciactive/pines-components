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
 * Manage customers using the user manager.
 *
 * @package Pines
 * @subpackage com_customer
 */
class com_customer extends component {
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
     * Gets a customer by GUID.
     *
     * @param int $id The customer's GUID.
     * @return entity|null The customer if it exists, null if it doesn't.
     */
    function get_customer($id) {
        global $config;
        $customer = $config->entity_manager->get_entity($id);
        if (is_null($customer) || !$customer->has_tag('com_customer', 'customer'))
            $customer = null;
        return $customer;
    }

    /**
     * Creates and attaches a module which lists users.
     */
	function list_customers() {
		global $config;

		$pgrid = new module('system', 'pgrid.default', 'content');
        $pgrid->icons = true;
        
		$module = new module('com_customer', 'list_customers', 'content');
		$module->title = "Customers";
        if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
            $module->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_customer/list_customers'];

		$module->customers = $config->entity_manager->get_entities_by_tags('com_customer', 'customer');

		if ( empty($module->customers) ) {
            $pgrid->detach();
            $module->detach();
            display_notice("There are no customers.");
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
		$module = new module('com_customer', 'form_customer', 'content');
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
}

?>