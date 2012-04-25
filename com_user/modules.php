<?php
/**
 * com_user's modules.
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

return array(
	'login' => array(
		'cname' => 'User Login Form',
		'description' => 'Log in to the system.',
		'view' => 'modules/login',
		'form' => 'modules/login_form',
		'type' => 'module imodule',
	),
	'current_user' => array(
		'cname' => 'Current User Display',
		'description' => 'Display information about the currently logged in user.',
		'view' => 'modules/current_user',
		'form' => 'modules/current_user_form',
		'type' => 'module imodule widget',
		'widget' => array(
			'default' => false,
			'depends' => array(
				'ability' => 'com_user/self',
			),
		),
	),
);

?>