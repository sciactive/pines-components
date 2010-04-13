<?php
/**
 * Delete a manufacturer.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/deletemanufacturer') )
	punt_user('You don\'t have necessary permission.', pines_url('com_sales', 'listmanufacturers'));

$list = explode(',', $_REQUEST['id']);
foreach ($list as $cur_manufacturer) {
	$cur_entity = com_sales_manufacturer::factory((int) $cur_manufacturer);
	if ( is_null($cur_entity->guid) || !$cur_entity->delete() )
		$failed_deletes .= (empty($failed_deletes) ? '' : ', ').$cur_manufacturer;
}
if (empty($failed_deletes)) {
	pines_notice('Selected manufacturer(s) deleted successfully.');
} else {
	pines_error('Could not delete manufacturers with given IDs: '.$failed_deletes);
}

$pines->com_sales->list_manufacturers();
?>