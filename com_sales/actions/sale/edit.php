<?php
/**
 * Provide a form to edit a sale.
 *
 * @package Components\sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if (!empty($_REQUEST['id'])) {
	if ( !gatekeeper('com_sales/editsale') )
		punt_user(null, pines_url('com_sales', 'sale/edit', array('id' => $_REQUEST['id'])));
} else {
	if ( !gatekeeper('com_sales/newsale') )
		punt_user(null, pines_url('com_sales', 'sale/edit'));
}

$entity = com_sales_sale::factory((int) $_REQUEST['id']);
$entity->print_form();

?>