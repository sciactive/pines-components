<?php
/**
 * Provide a form to make a package.
 *
 * @package Components
 * @subpackage packager
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_packager/makepackage') )
	punt_user(null, pines_url('com_packager', 'package/make', array('id' => $_REQUEST['id'])));


$module = new module('com_packager', 'package/result', 'content');
$module->results = array();
$list = explode(',', $_REQUEST['id']);
foreach ($list as $cur_package) {
	$cur_entity = com_packager_package::factory((int) $cur_package);
	if ( !isset($cur_entity->guid) )
		continue;
	$name = $cur_entity->get_filename();
	$filename = "{$name}.slm";
	$path = "{$pines->config->com_packager->package_path}{$filename}";
	$module->results[] = array(
		'entity' => $cur_entity,
		'filename' => $filename,
		'path' => $path,
		'result' => $cur_entity->package($path)
	);
}

?>