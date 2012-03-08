<?php
/**
 * com_user's buttons.
 *
 * @package Pines
 * @subpackage com_user
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'my_account' => array(
		'description' => 'Edit your account.',
		'text' => 'My Account',
		'class' => 'picon-user-identity',
		'href' => pines_url('com_user', 'editself'),
		'default' => true,
		'depends' => array(
			'ability' => 'com_user/self',
		),
	),
	'logout' => array(
		'description' => 'Logout.',
		'text' => 'Logout',
		'class' => 'picon-system-log-out',
		'href' => pines_url('com_user', 'logout'),
		'default' => true,
		'depends' => array(
			'ability' => '',
		),
	),
	'users' => array(
		'description' => 'User list.',
		'text' => 'Users',
		'class' => 'picon-system-users',
		'href' => pines_url('com_user', 'listusers'),
		'default' => false,
		'depends' => array(
			'ability' => 'com_user/listusers',
		),
	),
	'user_new' => array(
		'description' => 'New user.',
		'text' => 'User',
		'class' => 'picon-list-add-user',
		'href' => pines_url('com_user', 'edituser'),
		'default' => false,
		'depends' => array(
			'ability' => 'com_user/newuser',
		),
	),
	'groups' => array(
		'description' => 'Group list.',
		'text' => 'Groups',
		'class' => 'picon-system-users',
		'href' => pines_url('com_user', 'listgroups'),
		'default' => false,
		'depends' => array(
			'ability' => 'com_user/listgroups',
		),
	),
	'group_new' => array(
		'description' => 'New group.',
		'text' => 'Group',
		'class' => 'picon-user-group-new',
		'href' => pines_url('com_user', 'editgroup'),
		'default' => false,
		'depends' => array(
			'ability' => 'com_user/newgroup',
		),
	),
);

?>