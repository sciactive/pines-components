<?php
/**
 * List customers.
 *
 * @package Pines
 * @subpackage com_customertimer
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('system/all') )
	punt_user('You don\'t have necessary permission.', pines_url('com_customertimer', 'updateentities', null, false));

$entities = $pines->entity_manager->get_entities(array('tags' => array('com_customer_timer')));

foreach ($entities as $cur_entity) {
	$cur_entity->remove_tag('com_customer_timer');
	$cur_entity->add_tag('com_customertimer');
	$cur_entity->save();
}

$pines->page->override = true;
$pines->page->override_doc('done');

?>