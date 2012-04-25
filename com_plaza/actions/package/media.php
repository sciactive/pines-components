<?php
/**
 * Return a package's media file.
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

if ( !gatekeeper('com_plaza/listpackages') )
	punt_user(null, pines_url('com_plaza', 'package/list'));

$pines->page->override = true;
if ($_REQUEST['local'] == 'true') {
	$package = $pines->com_package->db['packages'][$_REQUEST['name']];
} else {
	$index = $pines->com_plaza->get_index(null, $_REQUEST['publisher']);
	$package = $index['packages'][$_REQUEST['name']];
}

if (isset($package)) {
	$media = $pines->com_plaza->package_get_media($package, $_REQUEST['media']);
	if (!$media)
		return;
	header('Content-Type: '.$media['content-type']);
	$pines->page->override_doc($media['data']);
}

?>