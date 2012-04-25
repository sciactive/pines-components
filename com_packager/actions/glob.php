<?php
/**
 * Search directories, returning JSON.
 *
 * @package Components
 * @subpackage packager
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_packager/newpackage') || !gatekeeper('com_packager/editpackage'))
	punt_user(null, pines_url('com_packager', 'glob'));

$pines->page->override = true;
header('Content-Type: application/json');

switch ($_REQUEST['type']) {
	case 'component':
		$prefix = clean_filename("components/{$_REQUEST['pkg_component']}/");
		break;
	case 'template':
		$prefix = clean_filename("templates/{$_REQUEST['pkg_template']}/");
		break;
	default:
		$prefix = '';
		break;
}

$files = glob($prefix.clean_filename($_REQUEST['q']).'*', GLOB_MARK);
foreach ($files as &$cur_file) {
	$cur_file = substr($cur_file, strlen($prefix));
}
unset($cur_file);

$pines->page->override_doc(json_encode($files));

?>