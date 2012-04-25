<?php
/**
 * Delete a package.
 *
 * @package Components\packager
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_packager/deletepackage') )
	punt_user(null, pines_url('com_packager', 'package/list'));

$list = explode(',', $_REQUEST['id']);
foreach ($list as $cur_package) {
	$cur_entity = com_packager_package::factory((int) $cur_package);
	if ( !isset($cur_entity->guid) || !$cur_entity->delete() )
		$failed_deletes .= (empty($failed_deletes) ? '' : ', ').$cur_package;
}
if (empty($failed_deletes)) {
	pines_notice('Selected package(s) deleted successfully.');
} else {
	pines_error('Could not delete packages with given IDs: '.$failed_deletes);
}

pines_redirect(pines_url('com_packager', 'package/list'));

?>