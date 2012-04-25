<?php
/**
 * Exit out of the current page.
 *
 * @package Components
 * @subpackage user
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

// Show some info if the user is still logged in.
if (is_object($_SESSION['user'])) {
	if ($_REQUEST['default'] == '1') {
		$module = new module('com_user', 'default_denied', 'content');
		pines_error('Incorrect user permission settings detected.');
		return;
	}
	if ( !empty($_REQUEST['url']) ) {
		$module = new module('com_user', 'punted', 'right');
		$module->url = urldecode($_REQUEST['url']);
	}

	// Load the user's default component.
	pines_action();
	return;
}

// If the user isn't logged in, let them.
$pines->user_manager->print_login();

?>