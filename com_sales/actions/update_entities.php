<?php
/**
 * Update sale entities.
 *
 * This is only a temporary file. It will be removed shortly.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('system/all') )
	punt_user();

$module = new module('system', 'null', 'content');
$module->title = 'Sale Entity ID Update';

$errors = array();
$offset = $count = $nochange = 0;
// Grab all sales, 50 at a time, and resave.
do {
	$entities = $pines->entity_manager->get_entities(
			array('limit' => 50, 'offset' => $offset, 'class' => com_sales_sale),
			array('&',
				'tag' => array('com_sales', 'sale')
			)
		);
	// If we have run through all entities, we are done updating.
	foreach ($entities as &$cur_entity) {
		if (!isset($cur_entity->id)) {
			if ($cur_entity->save())
				$count++;
			else
				$errors[] = $entity->guid;
		} else {
			$nochange++;
		}
	}
	unset($cur_entity);
	$offset += 50;
} while (!empty($entities));

$offset = 0;
// Grab all sales, 50 at a time, and resave.
do {
	$entities = $pines->entity_manager->get_entities(
			array('limit' => 50, 'offset' => $offset, 'class' => com_sales_stock),
			array('&',
				'tag' => array('com_sales', 'stock')
			)
		);
	// If we have run through all entities, we are done updating.
	foreach ($entities as &$cur_entity) {
		if (isset($cur_entity->status)) {
			$cur_entity->available = ($cur_entity->status == 'available');
			unset($cur_entity->status);
			if ($cur_entity->save())
				$count++;
			else
				$errors[] = $entity->guid;
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