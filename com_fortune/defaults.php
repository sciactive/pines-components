<?php
/**
 * com_fortune's configuration defaults.
 *
 * @package Pines
 * @subpackage com_fortune
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

return array(
	array(
		'name' => 'show',
		'cname' => 'Show Fortunes',
		'description' => 'Show a daily fortune.',
		'value' => false,
		'peruser' => true,
	),
	array(
		'name' => 'title',
		'cname' => 'Fortune Title',
		'description' => 'The title displayed on the fortune.',
		'value' => 'Fortune',
		'peruser' => true,
	),
	array(
		'name' => 'position',
		'cname' => 'Fortune Position',
		'description' => 'The position in which to place the fortune.',
		'value' => 'right',
		'options' => array(
			'header',
			'header_right',
			'left',
			'content_top_left',
			'content_top_right',
			'content',
			'content_bottom_left',
			'content_bottom_right',
			'right',
			'footer'
		),
		'peruser' => true,
	),
	array(
		'name' => 'databases',
		'cname' => 'Fortune Databases',
		'description' => 'The databases to search for fortunes.',
		'value' => pines_scandir('components/com_fortune/includes/'),
		'options' => pines_scandir('components/com_fortune/includes/'),
		'peruser' => true,
	),
);

?>