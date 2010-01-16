<?php
/**
 * Provide a form to edit a product.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if (isset($_REQUEST['id'])) {
	if ( !gatekeeper('com_sales/editproduct') )
		$config->user_manager->punt_user("You don't have necessary permission.", pines_url('com_sales', 'editproduct', array('id' => $_REQUEST['id']), false));
} else {
	if ( !gatekeeper('com_sales/newproduct') )
		$config->user_manager->punt_user("You don't have necessary permission.", pines_url('com_sales', 'editproduct', null, false));
}

$entity = com_sales_product::factory((int) $_REQUEST['id']);
$entity->print_form();

?>