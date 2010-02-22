<?php
/**
 * Delete a vendor.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/deletevendor') )
	punt_user('You don\'t have necessary permission.', pines_url('com_sales', 'listvendors', null, false));

$list = explode(',', $_REQUEST['id']);
foreach ($list as $cur_vendor) {
	$cur_entity = com_sales_vendor::factory((int) $cur_vendor);
	if ( is_null($cur_entity->guid) || !$cur_entity->delete() )
		$failed_deletes .= (empty($failed_deletes) ? '' : ', ').$cur_vendor;
}
if (empty($failed_deletes)) {
	display_notice('Selected vendor(s) deleted successfully.');
} else {
	display_error('Could not delete vendors with given IDs: '.$failed_deletes);
}

$pines->com_sales->list_vendors();
?>