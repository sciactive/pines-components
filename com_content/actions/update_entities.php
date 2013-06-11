<?php
/**
 * Update entities.
 *
 * @package Components\content
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('system/all') )
	punt_user(null, pines_url('com_content', 'update_entities'));

$entities = $pines->entity_manager->get_entities(
		array('class' => entity),
		array('&',
			'tag' => array('com_content'), 
			'tag' => array('category')
		)
	);

foreach ($entities as $cur_category) {
	if (!isset($cur_category->content_tags)) {
		$cur_category->content_tags = array('products');
	} else if ($cur_category->content_tags[0] == '' && !in_array('products', $cur_category->content_tags)) {
		$cur_category->content_tags[0] = 'products';
	} else if (!in_array('products', $cur_category->content_tags)) {
		$cur_category->content_tags[] = 'products';
	}
	$cur_category->save();
}

//$success = 0;
//$no_change = 0;
//$failed = array();
//foreach ($entities as $cur_entity) {
//	$changed = false;
//	if ($cur_entity->has_tag('page') && in_array('', $cur_entity->content_tags)) {
//		$cur_entity->content_tags = array_diff($cur_entity->content_tags, array(''));
//		$changed = true;
//	}
//	if (!isset($cur_entity->title_use_name)) {
//		$cur_entity->title_use_name = true;
//		$changed = true;
//	}
//	if ($changed) {
//		if ($cur_entity->save())
//			$success++;
//		else
//			$failed[] = htmlspecialchars($cur_entity->name);
//	} else
//		$no_change++;
//}
//
//$module = new module('system', 'null', 'content');
//$module->title = 'Entity Update';
//if ($success)
//	$module->content("<p>Updated $success entities successfully.</p>");
//if ($no_change)
//	$module->content("<p>Found $no_change entities that didn't require updating.</p>");
//if ($failed)
//	$module->content('<p>The following entities couldn\'t be updated:<ul><li>'.implode('</li><li>', $failed).'</li></ul></p>');

?>