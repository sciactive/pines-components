<?php
/**
 * Provide a form to edit a PO.
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
	if ( !gatekeeper('com_sales/editpo') )
		punt_user(null, pines_url('com_sales', 'po/edit', array('id' => $_REQUEST['id'])));
} else {
	if ( !gatekeeper('com_sales/newpo') )
		punt_user(null, pines_url('com_sales', 'po/edit'));
}

$entity = com_sales_po::factory((int) $_REQUEST['id']);
$entity->print_form();

?>