<?php
/**
 * com_menueditor's buttons.
 *
 * @package Pines
 * @subpackage com_menueditor
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'entries' => array(
		'description' => 'Menu entry list.',
		'text' => 'Menu',
		'class' => 'picon-configure-toolbars',
		'href' => pines_url('com_menueditor', 'entry/list'),
		'default' => true,
		'depends' => array(
			'ability' => 'com_menueditor/listentries',
		),
	),
	'entry_new' => array(
		'description' => 'New menu entry.',
		'text' => 'New Entry',
		'class' => 'picon-go-jump-locationbar',
		'href' => pines_url('com_menueditor', 'entry/edit'),
		'default' => true,
		'depends' => array(
			'ability' => 'com_menueditor/newentry',
		),
	),
);

?>