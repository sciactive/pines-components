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
 * Manage manufacturers using the user manager.
 *
 * @package Pines
 * @subpackage com_sales
 */
class com_sales extends component {
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
     * Gets a manufacturer by GUID.
     *
     * @param int $id The manufacturer's GUID.
     * @return entity|null The manufacturer if it exists, null if it doesn't.
     */
    function get_manufacturer($id) {
        global $config;
        $manufacturer = $config->entity_manager->get_entity($id);
        if (is_null($manufacturer) || !$manufacturer->has_tag('com_sales', 'manufacturer'))
            $manufacturer = null;
        return $manufacturer;
    }

    /**
     * Creates and attaches a module which lists users.
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
}

?>