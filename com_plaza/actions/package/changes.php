<?php
/**
 * Return changes required to perform an action on a package.
 *
 * @package Pines
 * @subpackage com_plaza
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_plaza/editpackages') )
	punt_user(null, pines_url('com_plaza', 'package/list'));

$pines->page->override = true;
header('Content-Type: application/json');
if ($_REQUEST['local'] == 'true') {
	$package = $pines->com_package->db['packages'][$_REQUEST['name']];
	$package['package'] = $_REQUEST['name'];
} else {
	$index = $pines->com_plaza->get_index(null, $_REQUEST['publisher']);
	$package = $index['packages'][$_REQUEST['name']];
}

$do = $_REQUEST['do'];
if (!isset($package) || !in_array($do, array('install', 'upgrade', 'remove')))
	return;

$pines->page->override_doc(json_encode($pines->com_plaza->calculate_changes_full($package, $do)));

?>