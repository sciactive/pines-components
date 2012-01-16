<?php
/**
 * Update entities.
 *
 * @package Pines
 * @subpackage com_sales
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
$module->title = 'Entity Update';

$errors = array();
$count = $nochange = 0;
// Grab entities and update.
$entities = $pines->entity_manager->get_entities(
		array('class' => com_sales_sale), // 'limit' => 50, 'offset' => $offset
		array('&',
			'tag' => array('com_sales', 'sale'),
			'data' => array('warehouse_items', true)
		),
		array('!&',
			'data' => array('warehouse_complete', true)
		)
	);

foreach ($entities as &$cur_entity) {
	if ($cur_entity->warehouse_items && !$cur_entity->warehouse_complete) {
		unset($cur_entity->warehouse_items);
		unset($cur_entity->warehouse_complete);
		$cur_entity->warehouse = true;
		if ($cur_entity->save())
			$count++;
		else
			$errors[] = $cur_entity->guid;
	} else {
		$nochange++;
	}
}
unset($cur_entity);

$module->content("Updated $count entities. Found $nochange entities that didn't need to be updated.");
if ($errors)
	$module->content('<br />Could not update the entities: '.implode(', ', $errors));

?>