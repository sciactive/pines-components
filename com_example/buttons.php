<?php
/**
 * com_example's buttons.
 *
 * @package Pines
 * @subpackage com_example
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'list_foobars' => array(
		'description' => 'List foobars.',
		'text' => 'Foobars',
		'class' => 'picon-view-pim-journal',
		'href' => pines_url('com_example', 'foobar/list'),
		'default' => false, // Set to true to show up in new dashboards.
		'depends' => array( // Only show up when these conditions are met.
			'ability' => 'com_example/listfoobars',
		),
	),
	'new_foobar' => array(
		'description' => 'Make a new foobar.',
		'text' => 'Foobar',
		'class' => 'picon-journal-new',
		'href' => pines_url('com_example', 'foobar/edit'),
		'default' => false,
		'depends' => array(
			'ability' => 'com_example/newfoobar',
		),
	),
	'content' => array(
		'description' => 'Show example content.',
		'text' => 'Content',
		'class' => 'picon-view-file-columns',
		'href' => pines_url('com_example', 'content'),
		'default' => false,
		'depends' => array(
			'ability' => 'com_example/content',
		),
	),
);

?>