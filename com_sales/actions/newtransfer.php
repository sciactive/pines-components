<?php
/**
 * Provide a form to create a new transfer.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/managestock') ) {
	$config->user_manager->punt_user("You don't have necessary permission.", pines_url('com_sales', 'newtransfer', array('id' => $_REQUEST['id']), false));
	return;
}

$list = explode(',', $_REQUEST['id']);

if (empty($list)) {
	display_notice('No inventory specified for transfer!');
	return;
}

$entity = new com_sales_transfer;
$entity->stock = array();
if (is_array($list)) {
	foreach ($list as $cur_stock_guid) {
		$cur_stock = $config->entity_manager->get_entity($cur_stock_guid, array('com_sales', 'stock'), com_sales_stock);
		if (!is_null($cur_stock)) {
			$entity->stock[] = $cur_stock_guid;
		}
	}
}
$entity->print_form();

?>