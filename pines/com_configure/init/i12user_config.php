<?php
/**
 * Load any user defined configuration.
 *
 * @package Pines
 * @subpackage com_configure
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if (!isset($_SESSION['user']))
	return;

$sys_config = array();
$com_config = array();
if ($_SESSION['user']->groups) {
	foreach ($_SESSION['user']->groups as &$cur_group) {
		if (is_array($cur_group->sys_config))
			$sys_config = array_merge($sys_config, $cur_group->sys_config);
		if (is_array($cur_group->com_config)) {
			foreach ($cur_group->com_config as $key => $cur_config) {
				$com_config[$key] = array_merge((array) $com_config[$key], $cur_config);
			}
		}
	}
	unset($cur_group);
}
if (is_array($_SESSION['user']->group->sys_config))
	$sys_config = array_merge($sys_config, $_SESSION['user']->group->sys_config);
if (is_array($_SESSION['user']->group->com_config)) {
	foreach ($_SESSION['user']->group->com_config as $key => $cur_config) {
		$com_config[$key] = array_merge((array) $com_config[$key], $cur_config);
	}
}
if (is_array($_SESSION['user']->sys_config))
	$sys_config = array_merge($sys_config, $_SESSION['user']->sys_config);
if (is_array($_SESSION['user']->com_config)) {
	foreach ($_SESSION['user']->com_config as $key => $cur_config) {
		$com_config[$key] = array_merge((array) $com_config[$key], $cur_config);
	}
}
unset($key);
unset($cur_config);
if ($sys_config || $com_config)
	$pines->configurator->load_per_user_array($sys_config, $com_config);
unset($sys_config);
unset($com_config);

?>