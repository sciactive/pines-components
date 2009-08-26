<?php
/**
 * Disable a component.
 *
 * @package Pines
 * @subpackage com_configure
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

if (in_array($_REQUEST['component'], $config->components)) {
    display_error('Given component is already enabled!');
    return;
}

if (!in_array($_REQUEST['component'], $config->all_components)) {
    display_error('Given component is not installed!');
    return;
}

if (rename('components/.'.$_REQUEST['component'], 'components/'.$_REQUEST['component'])) {
    $cur_loc = $config->template->url('com_configure', 'list', array('message' => urlencode('Component '.$_REQUEST['component'].' successfully enabled.')));
    header('Location: '.$cur_loc);
    return;
    // Don't add it to enabled yet, because it didn't have a chance to load itself.
    //$config->components[] = $_REQUEST['component'];
} else {
    display_error('Couldn\'t enable component '.$_REQUEST['component'].'.');
}

$config->configurator->list_components();

?>