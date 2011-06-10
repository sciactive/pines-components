<?php
/**
 * Shows category/page breadcrumbs.
 *
 * @package Pines
 * @subpackage com_content
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

$bc = '<span class="breadcrumb_item">'.htmlspecialchars($this->entity->name).'</span>';

if ($this->entity->has_tag('page')) {
	$categories = (array) $pines->entity_manager->get_entities(
			array('class' => com_content_category),
			array('&',
				'tag' => array('com_content', 'category'),
				'data' => array('enabled', true),
				'ref' => array('pages', $this->entity)
			)
		);
	foreach ($categories as $cur_entity) {
		if (!$cur_entity->ready())
			continue;
		if ($cur_entity->show_menu)
			break;
		$cur_parent = $cur_entity->parent;
		while (isset($cur_parent)) {
			if ($cur_parent->show_menu)
				break 2;
			$cur_parent = $cur_parent->parent;
		}
	}
} elseif ($this->entity->has_tag('category')) {
	$cur_entity = $this->entity->parent;
}

while (isset($cur_entity)) {
	$bc = '<a href="'.htmlspecialchars(pines_url('com_content', 'category', array('a' => $cur_entity->alias))).'" class="breadcrumb_item">'.htmlspecialchars($cur_entity->name).'</a> <span class="breadcrumb_sep">&gt;</span> ' . $bc;
	if ($cur_entity->show_menu)
		unset($cur_entity);
	else
		$cur_entity = $cur_entity->parent;
}

$bc = '<a href="'.htmlspecialchars(pines_url()).'" class="breadcrumb_item">Home</a> <span class="breadcrumb_sep">&gt;</span> ' . $bc;

echo $bc;
?>