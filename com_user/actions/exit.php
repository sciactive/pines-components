<?php
/**
 * Exit out of the current page and display a notice.
 *
 * @package Pines
 * @subpackage com_user
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

display_notice(stripslashes($_REQUEST['message']));
if (is_object($_SESSION['user'])) {
    // Load the user's default component.
    if ($_REQUEST['default'] == '1') {
        $module = new module('com_user', 'default_denied', 'content');
        $module->title = 'Incorrect User Permission Settings';
        display_error('Incorrect user permission settings detected.');
    } else {
        action($_SESSION['user']->default_component, 'default');
    }
} else {
	// Load the default component.
	action($config->default_component, 'default');
}
?>