<?php
/**
 * Delete a PO.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/deletepo') )
	punt_user(null, pines_url('com_sales', 'po/list'));

$list = explode(',', $_REQUEST['id']);
foreach ($list as $cur_po) {
	$cur_entity = com_sales_po::factory((int) $cur_po);
	if ( !isset($cur_entity->guid) || !$cur_entity->delete() )
		$failed_deletes .= (empty($failed_deletes) ? '' : ', ').$cur_po;
}
if (empty($failed_deletes)) {
	pines_notice('Selected PO(s) deleted successfully.');
} else {
	pines_error('Could not delete POs with given IDs: '.$failed_deletes);
	pines_notice('Note that POs cannot be deleted after items have been received on them.');
}

pines_redirect(pines_url('com_sales', 'po/list'));

?>