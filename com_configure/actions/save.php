<?php
/**
 * Description.
 *
 * @package Pines
 * @subpackage subpackage
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_configure/edit') ) {
	$config->user_manager->punt_user("You don't have necessary permission.", $config->template->url('com_configure', 'edit', $_GET, false));
	return;
}

if (!array_key_exists($_REQUEST['component'], $config->configurator->config_files)) {
    display_error('Given component either does not exist, or has no configuration file!');
    return;
}

if (!($cur_config_array = $config->configurator->get_config_array($config->configurator->config_files[$_REQUEST['component']]))) return;

foreach ($cur_config_array as $cur_key => $cur_var) {
    if (is_bool($cur_var['value'])) {
        $cur_config_array[$cur_key]['value'] = (($_REQUEST['opt_bool_'.$cur_var['name']] == 'ON') ? true : false);
    } elseif (is_int($cur_var['value'])) {
        $cur_config_array[$cur_key]['value'] = intval($_REQUEST['opt_int_'.$cur_var['name']]);
    } elseif (is_float($cur_var['value'])) {
        $cur_config_array[$cur_key]['value'] = floatval($_REQUEST['opt_float_'.$cur_var['name']]);
    } elseif (is_string($cur_var['value'])) {
        $cur_config_array[$cur_key]['value'] = strval($_REQUEST['opt_string_'.$cur_var['name']]);
    } else {
        $cur_config_array[$cur_key]['value'] = unserialize($_REQUEST['opt_serial_'.$cur_var['name']]);
    }
}

$config->configurator->put_config_array($cur_config_array, $config->configurator->config_files[$_REQUEST['component']]);

header('Location: '.$config->template->url('com_configure', 'list', null, false));

exit;

?>
