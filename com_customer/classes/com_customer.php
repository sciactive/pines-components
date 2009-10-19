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
		$module = new module('com_customer', 'customer_form', 'content');
		if ( is_null($id) ) {
			$module->username = $module->name = '';
		} else {
            
		}
        $module->heading = $heading;
        $module->new_option = $new_option;
        $module->new_action = $new_action;
        $module->id = $id;
	}
}

?>