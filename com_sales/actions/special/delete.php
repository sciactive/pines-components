<?php
/**
 * Delete a special.
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

if ( !gatekeeper('com_sales/deletespecial') )
	punt_user(null, pines_url('com_sales', 'special/list'));

$list = explode(',', $_REQUEST['id']);
foreach ($list as $cur_special) {
	$cur_entity = com_sales_special::factory((int) $cur_special);
	if ( !isset($cur_entity->guid) || !$cur_entity->delete() )
		$failed_deletes .= (empty($failed_deletes) ? '' : ', ').$cur_special;
}
if (empty($failed_deletes)) {
	pines_notice('Selected special(s) deleted successfully.');
} else {
	pines_error('Could not delete specials with given IDs: '.$failed_deletes);
}

pines_redirect(pines_url('com_sales', 'special/list'));

?>