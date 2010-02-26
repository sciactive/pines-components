<?php
/**
 * Import the entities in the database to a file.
 *
 * @package Pines
 * @subpackage com_entity
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('system/all') )
	punt_user('You don\'t have necessary permission.', pines_url('com_entity', 'import', null, false));

if (!empty($_FILES['entity_import']['tmp_name'])) {
	set_time_limit(3600);
	if ($pines->entity_manager->import($_FILES['entity_import']['tmp_name'])) {
		display_notice('Import complete.');
	} else {
		display_notice('Import failed.');
	}
}

$module = new module('com_entity', 'import', 'content');

?>