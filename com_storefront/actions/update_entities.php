<?php
/**
 * Update entities.
 *
 * @package Components\storefront
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('system/all') )
    punt_user('You don\'t have necessary permission.', pines_url());

$module = new module('system', 'null', 'content');
$module->title = 'Storefront Entity Update';

$errors = array();
$offset = $count = $nochange = 0;
// Grab entities, 50 at a time, and update.
do {
	$entities = $pines->entity_manager->get_entities(
			array('limit' => 50, 'offset' => $offset, 'class' => entity),
			array('&',
				'tag' => 'com_sales'
			),
			array('|',
				'tag' => array('product', 'category')
			)
		);

	foreach ($entities as &$cur_entity) {
		$changed = false;
		if ($cur_entity->has_tag('category') && !isset($cur_entity->show_title)) {
			$cur_entity->show_title = true;
			$changed = true;
		}
		if ($cur_entity->has_tag('category') && !isset($cur_entity->show_breadcrumbs)) {
			$cur_entity->show_breadcrumbs = true;
			$changed = true;
		}
		if ($cur_entity->has_tag('category') && !isset($cur_entity->show_products)) {
			$cur_entity->show_products = true;
			$changed = true;
		}
		if (!isset($cur_entity->alias)) {
			$alias = $cur_entity->name;
			$alias = preg_replace('/[^\w\d\s-.]/', '', $alias);
			$alias = preg_replace('/\s/', '-', $alias);
			$alias = strtolower($alias);
			$cur_entity->alias = $alias;
			$changed = true;
		}
		if ($changed) {
			if ($cur_entity->save())
				$count++;
			else
				$errors[] = $cur_entity->guid;
		} else {
			$nochange++;
		}
	}
	unset($cur_entity);
	$offset += 50;
} while (!empty($entities));

$module->content("Updated $count entities. Found $nochange entities that didn't need to be updated.");
if ($errors)
	$module->content('<br />Could not update the entities: '.implode(', ', $errors));

?>