<?php
/**
 * Show page menus.
 *
 * @package Pines
 * @subpackage com_content
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if (!$pines->config->com_content->show_page_menus)
	return;

$pages = (array) $pines->entity_manager->get_entities(
		array('class' => com_content_page),
		array('&',
			'data' => array('show_menu', true),
			'tag' => array('com_content', 'page')
		)
	);

if (!$pages)
	return;

foreach ($pages as $cur_page) {
	// Show the page in the menu.
	if (strpos($cur_page->menu_position, '/') === false) {
		// It's a new top level menu.
		$pines->menu->menu_arrays[] = array(
			'path' => "com_content_page_{$cur_page->guid}",
			'text' => $cur_page->name,
			'position' => $cur_page->menu_position
		);
	} else {
		// It's part of another menu.
		$pines->menu->menu_arrays[] = array(
			'path' => $cur_page->menu_position,
			'text' => $cur_page->name,
			'href' => array('com_content', 'page', array('a' => $cur_page->alias))
		);
	}
}

unset($pages, $cur_page);

?>