<?php

/*
 * com_cache's save action for new and existing directives.
 * 
 * @package Components\cache
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <amasiell.g@gmail.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');


$pines->page->override = true;

if ( !gatekeeper('com_cache/managecache') )
	$result = false;

if ($result !== false) { 
	if (isset($_REQUEST['save_global_exceptions'])) {
		$users = $_REQUEST['users'];
		$groups = $_REQUEST['groups'];
		$result = $pines->com_cache->save_global_exceptions($users, $groups);
	} else if (isset($_REQUEST['exceptions'])) {
		$component = $_REQUEST['component'];
		$action = $_REQUEST['caction'];
		$domain = $_REQUEST['domain'];
		$exceptions = json_decode($_REQUEST['exceptions'], true);
		$result = $pines->com_cache->save_exceptions($component, $action, $domain, $exceptions);
	} else if (isset($_REQUEST['delete_cacheoptions']) && !empty($_REQUEST['delete_cacheoptions'])) {
		unlink('system/cacheoptions.php');
		$pines->com_cache->destroy_dir($_REQUEST['delete_cacheoptions']);
		$result = true;
	} else if (isset($_REQUEST['cache_on'])) {
		$result = $pines->com_cache->changesettings(($_REQUEST['cache_on'] == 'On'));
	} else if (isset($_REQUEST['parent_directory'])) {
		$result = $pines->com_cache->changesettings(null, $_REQUEST['parent_directory']);
	} else if (isset($_REQUEST['component'])) {
		if ($_REQUEST['component'] == 'com_cache') // Do not cache com_cache
			$result = false;
		else {
			$directive = array();
			// Component
			$component = $_REQUEST['component'];
			$directive[$component] = array();
			// Action
			$action = $_REQUEST['caction']; // named to preserve url action
			$directive[$component][$action] = array();
			// Domain
			$domain = $_REQUEST['domain']; // named to preserve url action
			$directive[$component][$action][$domain] = array();

			// Options
			$directive[$component][$action][$domain]['time'] = (int) $_REQUEST['cachetime'];
			$directive[$component][$action][$domain]['cachequery'] = ($_REQUEST['cachequery'] == 'On');
			$directive[$component][$action][$domain]['cacheloggedin'] = ($_REQUEST['cacheloggedin'] == 'On');
			$directive[$component][$action][$domain]['disabled'] = ($_REQUEST['manage'] == 'disable') ? true : false;
			$directive[$component][$action][$domain]['exceptions'] = json_decode($_REQUEST['maintain_exceptions'], true);

			$delete = ($_REQUEST['manage'] == 'delete');
			$edit = ($_REQUEST['edit_directive'] == 'true');

			$result = $pines->com_cache->saveconfig($directive, $component, $action, $domain, $delete, $edit);
		}
	} else {
		$result = false;
	}
}

$pines->page->override_doc(json_encode($result));
?>