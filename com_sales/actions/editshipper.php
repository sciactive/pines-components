<?php
/**
 * Provide a form to edit a shipper.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/editshipper') ) {
	$config->user_manager->punt_user("You don't have necessary permission.", pines_url('com_sales', 'editshipper', array('id' => $_REQUEST['id']), false));
	return;
}

$config->run_sales->print_shipper_form('com_sales', 'saveshipper', $_REQUEST['id']);

?>