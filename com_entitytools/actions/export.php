<?php
/**
 * Export the entities in the entity manager to a file.
 *
 * @package Pines
 * @subpackage com_entitytools
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_entitytools/export') )
	punt_user(null, pines_url('com_entitytools', 'export'));

if (!is_callable(array($pines->entity_manager, 'export'))) {
	pines_notice('The currently installed entity manager doesn\'t support exporting.');
	return;
}

@set_time_limit(3600);
$pines->entity_manager->export_print();

?>