<?php
/**
 * Load any user/condition defined configuration.
 *
 * @package Components
 * @subpackage configure
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

$sys_config = array();
$com_config = array();

if (isset($pines->entity_manager) && $pines->config->com_configure->percondition) {
	$conditions = (array) $pines->entity_manager->get_entities(array('class' => com_configure_condition), array('&', 'tag' => array('com_configure', 'condition')));
	foreach ($conditions as &$cur_condition) {
		// Check that all conditions are met.
		$pass = true;
		foreach ($cur_condition->conditions as $cur_type => $cur_value) {
			if (!$pines->depend->check($cur_type, $cur_value)) {
				$pass = false;
				break;
			}
		}
		if (!$pass)
			continue;
		if ((array) $cur_condition->sys_config === $cur_condition->sys_config)
			$sys_config = array_merge($sys_config, $cur_condition->sys_config);
		if ((array) $cur_condition->com_config === $cur_condition->com_config) {
			foreach ($cur_condition->com_config as $key => $cur_config) {
				$com_config[$key] = array_merge((array) $com_config[$key], $cur_config);
			}
		}
	}
	unset($cur_condition, $conditions);
}

if (isset($_SESSION['user']) && $pines->config->com_configure->peruser) {
	if ((array) $_SESSION['user']->groups === $_SESSION['user']->groups) {
		foreach ($_SESSION['user']->groups as &$cur_group) {
			if ($pines->config->com_configure->conditional_groups && (array) $cur_group->conditions === $cur_group->conditions) {
				// Check that any group conditions are met.
				$pass = true;
				foreach ($cur_group->conditions as $cur_type => $cur_value) {
					if (!$pines->depend->check($cur_type, $cur_value)) {
						$pass = false;
						break;
					}
				}
				if (!$pass)
					continue;
			}
			if ((array) $cur_group->sys_config === $cur_group->sys_config)
				$sys_config = array_merge($sys_config, $cur_group->sys_config);
			if ((array) $cur_group->com_config === $cur_group->com_config) {
				foreach ($cur_group->com_config as $key => $cur_config) {
					$com_config[$key] = array_merge((array) $com_config[$key], $cur_config);
				}
			}
		}
		unset($cur_group);
	}
	if (isset($_SESSION['user']->group)) {
		$tmp_array = $_SESSION['user']->group->conditions;
		if ($pines->config->com_configure->conditional_groups && (array) $tmp_array === $tmp_array) {
			// Check that any group conditions are met.
			$pass = true;
			foreach ($tmp_array as $cur_type => $cur_value) {
				if (!$pines->depend->check($cur_type, $cur_value)) {
					$pass = false;
					break;
				}
			}
		} else {
			$pass = true;
		}
		if ($pass) {
			$tmp_array = $_SESSION['user']->group->sys_config;
			if ((array) $tmp_array === $tmp_array)
				$sys_config = array_merge($sys_config, $tmp_array);
			$tmp_array = $_SESSION['user']->group->com_config;
			if ((array) $tmp_array === $tmp_array) {
				foreach ($tmp_array as $key => $cur_config) {
					$com_config[$key] = array_merge((array) $com_config[$key], $cur_config);
				}
			}
		}
	}
	if ((array) $_SESSION['user']->sys_config === $_SESSION['user']->sys_config)
		$sys_config = array_merge($sys_config, $_SESSION['user']->sys_config);
	if ((array) $_SESSION['user']->com_config === $_SESSION['user']->com_config) {
		foreach ($_SESSION['user']->com_config as $key => $cur_config) {
			$com_config[$key] = array_merge((array) $com_config[$key], $cur_config);
		}
	}
}

if ($sys_config || $com_config)
	$pines->configurator->load_per_user_array($sys_config, $com_config);
unset($sys_config, $com_config, $key, $cur_config, $tmp_array, $pass, $cur_type, $cur_value);

?>