<?php
/**
 * Exit out of the current page and display a notice.
 *
 * @package Pines
 * @subpackage com_user
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if (!empty($_REQUEST['message']))
	display_notice($_REQUEST['message']);

// Show some info if the user is still logged in.
if (is_object($_SESSION['user'])) {
	if ($_REQUEST['default'] == '1') {
		$module = new module('com_user', 'default_denied', 'content');
		display_error('Incorrect user permission settings detected.');
		return;
	}
	if ( !empty($_REQUEST['url']) ) {
		$module = new module('com_user', 'punted', 'right');
		$module->url = urldecode($_REQUEST['url']);
	}
}

// Load the user's default component.
action($pines->config->default_component, 'default');

?>