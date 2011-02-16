<?php
/**
 * com_timeoutnotice's configuration defaults.
 *
 * @package Pines
 * @subpackage com_timeoutnotice
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

return array(
	array(
		'name' => 'timeout',
		'cname' => 'Timeout',
		'description' => 'Timeout in seconds.',
		'value' => 600,
		'peruser' => true,
	),
	array(
		'name' => 'action',
		'cname' => 'Timeout Action',
		'description' => 'The action to do after the user times out. "Login Dialog" shows a dialog to let them login without leaving the page. This may cause problems with some components that use session variables.',
		'value' => 'dialog',
		'options' => array(
			'Login Dialog' => 'dialog',
			'Refresh Page' => 'refresh',
			'Redirect to URL' => 'redirect',
		),
		'peruser' => true,
	),
	array(
		'name' => 'redirect_url',
		'cname' => 'Redirect URL',
		'description' => 'The URL to redirect the user to when they time out and the action is "Redirect to URL".',
		'value' => $pines->config->rela_location,
		'peruser' => true,
	),
);

?>