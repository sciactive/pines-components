<?php
/**
 * Delete an page.
 *
 * @package Pines
 * @subpackage com_content
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_content/deletepage') )
	punt_user('You don\'t have necessary permission.', pines_url('com_content', 'page/list'));

$list = explode(',', $_REQUEST['id']);
foreach ($list as $cur_page) {
	$cur_entity = com_content_page::factory((int) $cur_page);
	if ( !isset($cur_entity->guid) || !$cur_entity->delete() )
		$failed_deletes .= (empty($failed_deletes) ? '' : ', ').$cur_page;
}
if (empty($failed_deletes)) {
	pines_notice('Selected page(s) deleted successfully.');
} else {
	pines_error('Could not delete pages with given IDs: '.$failed_deletes);
}

redirect(pines_url('com_content', 'page/list'));

?>