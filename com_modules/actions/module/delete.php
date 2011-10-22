<?php
/**
 * Delete a module.
 *
 * @package Pines
 * @subpackage com_modules
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_modules/deletemodule') )
	punt_user(null, pines_url('com_modules', 'module/list'));

$list = explode(',', $_REQUEST['id']);
foreach ($list as $cur_module) {
	$cur_entity = com_modules_module::factory((int) $cur_module);
	if ( !isset($cur_entity->guid) || !$cur_entity->delete() )
		$failed_deletes .= (empty($failed_deletes) ? '' : ', ').$cur_module;
}
if (empty($failed_deletes)) {
	pines_notice('Selected module(s) deleted successfully.');
} else {
	pines_error('Could not delete modules with given IDs: '.$failed_deletes);
}

pines_redirect(pines_url('com_modules', 'module/list'));

?>