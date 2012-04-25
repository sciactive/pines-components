<?php
/**
 * Show menu entries.
 *
 * @package Components\menueditor
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

$entries = (array) $pines->entity_manager->get_entities(
		array('class' => com_menueditor_entry),
		array('&',
			'tag' => array('com_menueditor', 'entry'),
			'strict' => array('enabled', true)
		)
	);

$pines->entity_manager->sort($entries, 'sort_order');

foreach ($entries as $cur_entry)
	$pines->menu->menu_arrays[] = $cur_entry->menu_array();

unset($entries, $cur_entry);

?>