<?php
/**
 * Delete a vendor.
 *
 * @package Components
 * @subpackage sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/deletevendor') )
	punt_user(null, pines_url('com_sales', 'vendor/list'));

$list = explode(',', $_REQUEST['id']);
foreach ($list as $cur_vendor) {
	$cur_entity = com_sales_vendor::factory((int) $cur_vendor);
	if ( !isset($cur_entity->guid) || !$cur_entity->delete() )
		$failed_deletes .= (empty($failed_deletes) ? '' : ', ').$cur_vendor;
}
if (empty($failed_deletes)) {
	pines_notice('Selected vendor(s) deleted successfully.');
} else {
	pines_error('Could not delete vendors with given IDs: '.$failed_deletes);
}

pines_redirect(pines_url('com_sales', 'vendor/list'));

?>