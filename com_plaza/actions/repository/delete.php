<?php
/**
 * Delete a repository.
 *
 * @package Components
 * @subpackage plaza
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_plaza/editrepositories') )
	punt_user(null, pines_url('com_plaza', 'repository/list'));

$list = explode(',', $_REQUEST['id']);
foreach ($list as $cur_repository) {
	$filename = basename(clean_filename($cur_repository));
	$path = "components/com_plaza/includes/cache/certs/repositories/{$filename}";
	if ( !file_exists($path) || !unlink($path) )
		$failed_deletes .= (empty($failed_deletes) ? '' : ', ').$filename;
}
if (empty($failed_deletes)) {
	pines_notice('Selected repository(s) deleted successfully.');
} else {
	pines_error('Could not delete repositorys: '.$failed_deletes);
}

pines_redirect(pines_url('com_plaza', 'repository/list'));

?>