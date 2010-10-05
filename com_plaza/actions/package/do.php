<?php
/**
 * Perform an action on a package.
 *
 * @package Pines
 * @subpackage com_plaza
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_plaza/editpackages') )
	punt_user(null, pines_url('com_plaza', 'package/list'));

$pines->page->override = true;
if ($_REQUEST['local'] == 'true') {
	$package = $pines->com_package->db['packages'][$_REQUEST['name']];
} else {
	$index = $pines->com_plaza->get_index(null, $_REQUEST['publisher']);
	$package = $index['packages'][$_REQUEST['name']];
}

$do = $_REQUEST['do'];
if (!isset($package) || !in_array($do, array('install', 'upgrade', 'remove', 'reinstall')))
	return;

if ($do != 'reinstall') {
	$changes = $pines->com_plaza->calculate_changes_full($package, $do);
	if (!$changes['possible'] || $changes['service'])
		return;
}

switch ($do) {
	case 'install':
		do {
			$passed = true;
			$old_changes = $changes;
			if ($changes['install']) {
				foreach ($changes['install'] as $key => $cur_package_name) {
					$cur_package = $index['packages'][$cur_package_name];
					if (!$pines->com_plaza->package_install($cur_package)) {
						$passed = false;
					} else {
						unset($changes['install'][$key]);
					}
				}
			}
			if ($changes['remove']) {
				foreach ($changes['remove'] as $key => $cur_package_name) {
					$cur_package = $pines->com_package->db['packages'][$cur_package_name];
					if (!$pines->com_plaza->package_remove($cur_package)) {
						$passed = false;
					} else {
						unset($changes['remove'][$key]);
					}
				}
			}
			// Check that we're not just looping.
			if ($changes === $old_changes)
				break;
		} while (!$passed);
		if ($passed) {
			$return = $pines->com_plaza->package_install($package);
		} else {
			$return = false;
		}
		break;
	case 'upgrade':
		break;
	case 'remove':
		$return = $pines->com_plaza->package_remove($package);
		break;
	case 'reinstall':
		break;
}

$pines->page->override_doc(json_encode($return));

?>