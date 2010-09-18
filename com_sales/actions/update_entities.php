<?php
/**
 * Update product image descriptions.
 *
 * This is only a temporary file. It will be removed shortly.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('system/all') )
	punt_user();

$module = new module('system', 'null', 'content');
$module->title = 'Product Image Description Update';

$errors = array();
$offset = $count = $nochange = 0;
// Grab all products, 50 at a time, and resave.
do {
	$entities = $pines->entity_manager->get_entities(
			array('limit' => 50, 'offset' => $offset, 'class' => com_sales_product),
			array('&',
				'tag' => array('com_sales', 'product'),
				'data' => array('images', true)
			)
		);

	foreach ($entities as &$cur_entity) {
		$changed = false;
		foreach ($cur_entity->images as &$cur_image) {
			if ($cur_image['alt'] == 'Click to edit description...') {
				$cur_image['alt'] = '';
				$changed = true;
			}
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