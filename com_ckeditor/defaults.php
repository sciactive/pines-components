<?php
/**
 * com_ckeditor's configuration defaults.
 *
 * @package Pines
 * @subpackage com_ckeditor
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
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
		'name' => 'auto_scayt',
		'cname' => 'Auto Spell Check',
		'description' => 'Turn on auto spell check as you type by default.',
		'value' => true,
		'peruser' => true,
	),
);

?>