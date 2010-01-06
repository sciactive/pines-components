<?php
/**
 * Delete a manufacturer.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/deletemanufacturer') ) {
	$config->user_manager->punt_user("You don't have necessary permission.", pines_url('com_sales', 'listmanufacturers', null, false));
	return;
}

$list = explode(',', $_REQUEST['id']);
foreach ($list as $cur_manufacturer) {
	$cur_entity = new com_sales_manufacturer((int) $cur_manufacturer);
	if ( is_null($cur_entity->guid) || !$cur_entity->delete() )
		$failed_deletes .= (empty($failed_deletes) ? '' : ', ').$cur_manufacturer;
}
if (empty($failed_deletes)) {
	display_notice('Selected manufacturer(s) deleted successfully.');
} else {
	display_error('Could not delete manufacturers with given IDs: '.$failed_deletes);
}

$config->run_sales->list_manufacturers();
?>