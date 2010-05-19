<?php
/**
 * Open and resave all entities.
 *
 * @package Pines
 * @subpackage com_entitytools
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_entitytools/test') )
	punt_user('You don\'t have necessary permission.', pines_url('com_entitytools', 'resave'));

$module = new module('system', 'null', 'content');
$module->title = 'Entity Resave';

$errors = array();
$offset = $count = $nochange = 0;
// Grab all entities, 50 at a time, and resave them.
do {
	$entities = $pines->entity_manager->get_entities(array('limit' => 50, 'offset' => $offset));
	// If we have run through all entities, we are done updating.
	foreach ($entities as &$cur_entity) {
		if ($cur_entity->save())
			$count++;
		else
			$errors[] = $entity->guid;
	}
	unset($cur_entity);
	$offset += 50;
} while (!empty($entities));

$module->content("Resaved $count entities.");
if ($errors)
	$module->content('<br />Could not save the entities: '.implode(', ', $errors));

?>