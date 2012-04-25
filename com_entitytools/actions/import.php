<?php
/**
 * Import entities from a file into the entity manager.
 *
 * @package Components\entitytools
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_entitytools/import') )
	punt_user(null, pines_url('com_entitytools', 'import'));

if (!is_callable(array($pines->entity_manager, 'import'))) {
	pines_notice('The currently installed entity manager doesn\'t support importing.');
	return;
}

if (!empty($_FILES['entity_import']['tmp_name'])) {
	set_time_limit(3600);
	if ($pines->entity_manager->import($_FILES['entity_import']['tmp_name'])) {
		pines_notice('Import complete.');
	} else {
		pines_notice('Import failed.');
	}
}

$module = new module('com_entitytools', 'import', 'content');

?>