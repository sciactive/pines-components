<?php
/**
 * Provide a form to edit a condition.
 *
 * @package Pines
 * @subpackage com_configure
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_configure/edit') )
	punt_user(null, pines_url('com_configure', 'condition/edit', array('id' => $_REQUEST['id'])));

$entity = com_configure_condition::factory((int) $_REQUEST['id']);
$entity->print_form();

?>