<?php
/**
 * Update entities.
 *
 * @package Components\pgentity
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
$offset = $count = 0;
// Grab all entities, 50 at a time, and resave.
do {
	$entities = $pines->entity_manager->get_entities(
			array('limit' => 50, 'offset' => $offset)
		);

	foreach ($entities as &$cur_entity) {
		if ($cur_entity->save())
			$count++;
		else
			$errors[] = $cur_entity->guid;
	}
	unset($cur_entity);
	$offset += 50;
} while (!empty($entities));

$module->content("Updated $count entities.");
if ($errors)
	$module->content('<br />Could not update the entities: '.implode(', ', $errors));

?>