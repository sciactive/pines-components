<?php
/**
 * com_ckeditor's configuration defaults.
 *
 * @package Components
 * @subpackage ckeditor
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	array(
		'name' => 'toolbar',
		'cname' => 'Toolbar',
		'description' => 'The included plugins and layout of the buttons on the regular editor\'s toolbar.',
		'value' => 'Full',
		'options' => array(
			'Full Featured' => 'Full',
			'Minimal' => 'Basic',
		),
		'peruser' => true,
	),
	array(
		'name' => 'ui_color',
		'cname' => 'User Interface Color',
		'description' => 'The color of the user interface. Use an HTML color code.',
		'value' => '#E0E0E0',
		'peruser' => true,
	),
	array(
		'name' => 'default_mode',
		'cname' => 'Default Mode',
		'description' => 'The mode in which to start the editor.',
		'value' => 'wysiwyg',
		'options' => array(
			'WYSIWYG' => 'wysiwyg',
			'Source Code' => 'source',
		),
		'peruser' => true,
	),
	array(
		'name' => 'show_blocks',
		'cname' => 'Show Blocks',
		'description' => 'Show blocks in the editor by default.',
		'value' => false,
		'peruser' => true,
	),
	array(
		'name' => 'auto_scayt',
		'cname' => 'Auto Spell Check',
		'description' => 'Turn on auto spell check as you type by default. (May cause problems when switching between source view.)',
		'value' => false,
		'peruser' => true,
	),
);

?>