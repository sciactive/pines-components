<?php
/**
 * Delete a package.
 *
 * @package Pines
 * @subpackage com_repository
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_repository/deletepackage') )
	punt_user(null, pines_url('com_repository', 'listpackages'));

$list = explode(',', $_REQUEST['id']);
foreach ($list as $cur_package) {
	// Delete package.
	$failed_deletes .= (empty($failed_deletes) ? '' : ', ').$cur_package;
}
if (empty($failed_deletes)) {
	pines_notice('Selected package(s) deleted successfully.');
} else {
	pines_error('Could not delete packages with given IDs: '.$failed_deletes);
}

redirect(pines_url('com_repository', 'listpackages'));

?>