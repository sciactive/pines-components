<?php
/**
 * Provide a form to edit a manufacturer.
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
	if ( !gatekeeper('com_sales/editmanufacturer') )
		punt_user('You don\'t have necessary permission.', pines_url('com_sales', 'editmanufacturer', array('id' => $_REQUEST['id']), false));
} else {
	if ( !gatekeeper('com_sales/newmanufacturer') )
		punt_user('You don\'t have necessary permission.', pines_url('com_sales', 'editmanufacturer', null, false));
}

$entity = com_sales_manufacturer::factory((int) $_REQUEST['id']);
$entity->print_form();

?>