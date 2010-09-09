<?php
/**
 * Provide a form to edit a floor.
 *
 * @package Pines
 * @subpackage com_customertimer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if (isset($_REQUEST['id'])) {
	if ( !gatekeeper('com_customertimer/editfloor') )
		punt_user(null, pines_url('com_customertimer', 'editfloor', array('id' => $_REQUEST['id'])));
} else {
	if ( !gatekeeper('com_customertimer/newfloor') )
		punt_user(null, pines_url('com_customertimer', 'editfloor'));
}

$entity = com_customertimer_floor::factory((int) $_REQUEST['id']);
$entity->print_form();

?>