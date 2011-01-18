<?php
/**
 * Provide a form to edit a replacement.
 *
 * @package Pines
 * @subpackage com_replace
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if (isset($_REQUEST['id'])) {
	if ( !gatekeeper('com_replace/editreplacement') )
		punt_user(null, pines_url('com_replace', 'replacement/edit', array('id' => $_REQUEST['id'])));
} else {
	if ( !gatekeeper('com_replace/newreplacement') )
		punt_user(null, pines_url('com_replace', 'replacement/edit'));
}

$entity = com_replace_replacement::factory((int) $_REQUEST['id']);
$entity->print_form();

?>