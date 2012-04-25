<?php
/**
 * tpl_joomlatemplates' configuration.
 *
 * @package Templates\joomlatemplates
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	array(
		'name' => 'template',
		'cname' => 'Template',
		'description' => 'Joomla! template to use.',
		'value' => 'rhuk_milkyway',
		'options' => array_diff(pines_scandir('templates/tpl_joomlatemplates/templates/'), array('system')),
		'peruser' => true,
	),
	array(
		'name' => 'main_menu_position',
		'cname' => 'Main Menu Position',
		'description' => 'Because Joomla! doesn\'t have a specific position for the main menu, you need to choose one which will then be dedicated to the main menu.',
		'value' => 'user3',//'hornav',
		'peruser' => true,
	),
	array(
		'name' => 'module_notes',
		'cname' => 'Module Note Behavior',
		'description' => 'Joomla! modules also don\'t have notes, so you need to choose where to put module notes.',
		'value' => 'title',
		'options' => array(
			'Under the title.' => 'title',
			'Before the content.' => 'content',
			'Ignore the module notes.' => 'ignore',
		),
		'peruser' => true,
	),
	array(
		'name' => 'content_style',
		'cname' => 'Content Chrome Style',
		'description' => 'Pines\' "content" position is placed where Joomla! component content goes, but it has no style associated. You can pick from the default styles here.',
		'value' => 'xhtml',
		'options' => array(
			'none',
			'table',
			'horz',
			'xhtml',
			'rounded',
		),
		'peruser' => true,
	),
	array(
		'name' => 'language',
		'cname' => 'Language',
		'description' => 'Joomla! template language.',
		'value' => 'en-us',
		'peruser' => true,
	),
	array(
		'name' => 'direction',
		'cname' => 'Direction',
		'description' => 'Joomla! template direction.',
		'value' => 'ltr',
		'options' => array(
			'ltr',
			'rtl'
		),
		'peruser' => true,
	),
);

?>