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
		array('class' => entity),
		array('&',
			'tag' => array('com_sales')
		),
		array('|',
			'tag' => array('category', 'product')
		)
	);

foreach ($entities as &$cur_entity) {
	if (!isset($cur_entity->title_use_name)) {
		$cur_entity->title_use_name = true;
		$cur_entity->title_position = 'prepend';
		$cur_entity->meta_tags = array();
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