<?php
/**
 * com_configure's buttons.
 *
 * @package Pines
 * @subpackage com_configure
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'config' => array(
		'description' => 'Configuration.',
		'text' => 'Config',
		'class' => 'picon-preferences-system',
		'href' => pines_url('com_configure', 'list'),
		'default' => true,
		'depends' => array(
			'ability' => 'com_configure/edit|com_configure/view',
		),
	),
	'config_conditional' => array(
		'description' => 'Conditional configuration.',
		'text' => 'Cond. Config',
		'class' => 'picon-preferences-other',
		'href' => pines_url('com_configure', 'list', array('percondition' => 'true')),
		'default' => false,
		'depends' => array(
			'ability' => 'com_configure/edit|com_configure/view',
		),
	),
	'config_user' => array(
		'description' => 'Per-user and per-group configuration.',
		'text' => 'User Config',
		'class' => 'picon-preferences-desktop-user',
		'href' => pines_url('com_configure', 'list', array('peruser' => 'true')),
		'default' => false,
		'depends' => array(
			'ability' => 'com_configure/editperuser|com_configure/viewperuser',
		),
	),
);

?>