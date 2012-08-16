<?php
/**
 * com_notes' configuration defaults.
 *
 * @package Components\notes
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	array(
		'name' => 'editor_position',
		'cname' => 'Editor Position',
		'description' => 'The position on the page of the note editor.',
		'value' => 'right',
		'peruser' => true,
	),
	array(
		'name' => 'editor_sort_order',
		'cname' => 'Editor Sort Order',
		'description' => 'The sort order of the threads in the editor.',
		'value' => 'desc',
		'options' => array(
			'Oldest first. (Ascending)' => 'asc',
			'Newest first. (Descending)' => 'desc'
		),
		'peruser' => true,
	),
);

?>