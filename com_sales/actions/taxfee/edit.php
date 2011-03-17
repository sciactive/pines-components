<?php
/**
 * Provide a form to edit a tax/fee.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if (!empty($_REQUEST['id'])) {
	if ( !gatekeeper('com_sales/edittaxfee') )
		punt_user(null, pines_url('com_sales', 'taxfee/edit', array('id' => $_REQUEST['id'])));
} else {
	if ( !gatekeeper('com_sales/newtaxfee') )
		punt_user(null, pines_url('com_sales', 'taxfee/edit'));
}

$entity = com_sales_tax_fee::factory((int) $_REQUEST['id']);
$entity->print_form();

?>