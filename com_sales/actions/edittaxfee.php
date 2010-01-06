<?php
/**
 * Provide a form to edit a tax/fee.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/edittaxfee') ) {
	$config->user_manager->punt_user("You don't have necessary permission.", pines_url('com_sales', 'edittaxfee', array('id' => $_REQUEST['id']), false));
	return;
}

$entity = com_sales_tax_fee::factory((int) $_REQUEST['id']);
$entity->print_form();

?>