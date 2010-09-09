<?php
/**
 * Provide a form to edit a manufacturer.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if (isset($_REQUEST['id'])) {
	if ( !gatekeeper('com_sales/editmanufacturer') )
		punt_user(null, pines_url('com_sales', 'manufacturer/edit', array('id' => $_REQUEST['id'])));
} else {
	if ( !gatekeeper('com_sales/newmanufacturer') )
		punt_user(null, pines_url('com_sales', 'manufacturer/edit'));
}

$entity = com_sales_manufacturer::factory((int) $_REQUEST['id']);
$entity->print_form();

?>