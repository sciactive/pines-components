<?php
/**
 * Save the given configuration.
 *
 * @package Pines
 * @subpackage com_configure
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_configure/edit') )
	punt_user('You don\'t have necessary permission.', pines_url('com_configure', 'edit', $_GET));

if (!array_key_exists($_REQUEST['component'], $pines->configurator->component_files)) {
	pines_error('Given component either does not exist, or has no configuration file!');
	return;
}

$component = configurator_component::factory($_REQUEST['component']);
if ($_REQUEST['peruser']) {
	if ($_REQUEST['type'] == 'group') {
		$user = group::factory((int) $_REQUEST['id']);
	} else {
		$user = user::factory((int) $_REQUEST['id']);
	}
	if (!isset($user->guid)) {
		pines_error('Requested user/group id is not accessible.');
		return;
	}
	$component->set_per_user($user);
}
$component->config = array();

foreach ($component->defaults as $cur_var) {
	if ($_REQUEST['manset_'.$cur_var['name']] != 'ON')
		continue;
	if (is_array($cur_var['options'])) {
		if (is_array($cur_var['value'])) {
			$rvalue = (array) $_REQUEST['opt_multi_'.$cur_var['name']];
			foreach ($rvalue as &$cur_rvalue) {
				$cur_rvalue = unserialize($cur_rvalue);
			}
			unset($cur_rvalue);
			foreach ($rvalue as $cur_rkey => $cur_rvalue) {
				if (!in_array($cur_rvalue, $cur_var['options']))
					unset($rvalue[$cur_rkey]);
			}
			$component->config[] = array('name' => $cur_var['name'], 'value' => $rvalue);
		} else {
			$rvalue = unserialize($_REQUEST['opt_multi_'.$cur_var['name']]);
			foreach ($cur_var['options'] as $cur_option) {
				if ($rvalue === $cur_option) {
					$component->config[] = array('name' => $cur_var['name'], 'value' => $rvalue);
					break;
				}
			}
		}
	} elseif (is_array($cur_var['value'])) {
		if (is_int($cur_var['value'][0])) {
			$rvalue = (array) explode(';;', $_REQUEST['opt_int_'.$cur_var['name']]);
			foreach ($rvalue as &$cur_rvalue) {
				$cur_rvalue = (int) $cur_rvalue;
			}
			unset($cur_rvalue);
			$component->config[] = array('name' => $cur_var['name'], 'value' => $rvalue);
		} elseif (is_float($cur_var['value'][0])) {
			$rvalue = (array) explode(';;', $_REQUEST['opt_float_'.$cur_var['name']]);
			foreach ($rvalue as &$cur_rvalue) {
				$cur_rvalue = (float) $cur_rvalue;
			}
			unset($cur_rvalue);
			$component->config[] = array('name' => $cur_var['name'], 'value' => $rvalue);
		} elseif (is_string($cur_var['value'][0])) {
			$rvalue = (array) explode(';;', $_REQUEST['opt_string_'.$cur_var['name']]);
			foreach ($rvalue as &$cur_rvalue) {
				$cur_rvalue = (string) $cur_rvalue;
			}
			unset($cur_rvalue);
			$component->config[] = array('name' => $cur_var['name'], 'value' => $rvalue);
		}
	} elseif (is_bool($cur_var['value'])) {
		$component->config[] = array(
			'name' => $cur_var['name'],
			'value' => ($_REQUEST['opt_bool_'.$cur_var['name']] == 'ON')
		);
	} elseif (is_int($cur_var['value'])) {
		$component->config[] = array(
			'name' => $cur_var['name'],
			'value' => (int) $_REQUEST['opt_int_'.$cur_var['name']]
		);
	} elseif (is_float($cur_var['value'])) {
		$component->config[] = array(
			'name' => $cur_var['name'],
			'value' => (float) $_REQUEST['opt_float_'.$cur_var['name']]
		);
	} elseif (is_string($cur_var['value'])) {
		$component->config[] = array(
			'name' => $cur_var['name'],
			'value' => (string) $_REQUEST['opt_string_'.$cur_var['name']]
		);
	} else {
		$component->config[] = array(
			'name' => $cur_var['name'],
			'value' => unserialize($_REQUEST['opt_serial_'.$cur_var['name']])
		);
	}
}

if (!$component->save_config()) {
	pines_error('Config could not be saved.');
	$component->print_form();
	return;
}

pines_notice("Config saved for {$component->name}.");

if ($component->per_user) {
	if ($_SESSION['user'])
		$_SESSION['user']->refresh();
	redirect(pines_url('com_configure', 'list', array('peruser' => 1, 'type' => $component->type, 'id' => $component->user->guid)));
} else {
	redirect(pines_url('com_configure', 'list'));
}

?>