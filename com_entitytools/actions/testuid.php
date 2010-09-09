<?php
/**
 * Test an entity manager's UID functions.
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
	punt_user(null, pines_url('com_entitytools', 'testuid'));

$name = 'com_entitytools/uid_test_'.time();
$id = $pines->entity_manager->new_uid($name);
var_dump($id);
$id = $pines->entity_manager->get_uid($name);
var_dump($id);
$pines->entity_manager->set_uid($name, 12);
$id = $pines->entity_manager->get_uid($name);
var_dump($id);
$id = $pines->entity_manager->new_uid($name);
var_dump($id);
$id = $pines->entity_manager->new_uid($name.'a');
var_dump($id);
$id = $pines->entity_manager->new_uid($name.'b');
var_dump($id);
$pines->entity_manager->rename_uid($name, $name.'c');
$id = $pines->entity_manager->get_uid($name);
var_dump($id);
$id = $pines->entity_manager->get_uid($name.'c');
var_dump($id);

$pines->entity_manager->delete_uid($name.'a');
$pines->entity_manager->delete_uid($name.'b');
$pines->entity_manager->delete_uid($name.'c');

$id = $pines->entity_manager->get_uid($name);
var_dump($id);
$id = $pines->entity_manager->get_uid($name.'a');
var_dump($id);
$id = $pines->entity_manager->get_uid($name.'b');
var_dump($id);
$id = $pines->entity_manager->get_uid($name.'c');
var_dump($id);

$pines->page->override = true;

?>