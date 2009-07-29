<?php
/**
 * com_entity's common file.
 *
 * @package Pines
 * @subpackage com_entity
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

$config->entity_manager = new com_entity;
/* TODO: should entities have ability management?
$config->ability_manager->add('com_entity', 'new', 'New Entities', 'Let user create new entities.');
$config->ability_manager->add('com_entity', 'edit', 'Edit Entities', 'Let user edit entities.');
$config->ability_manager->add('com_entity', 'view', 'View Entities', 'Let user view entities.');
*/

?>