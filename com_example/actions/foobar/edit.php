<?php
/**
 * Provide a form to edit a foobar.
 *
 * @package Components\example
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if (!empty($_REQUEST['id'])) {
	if ( !gatekeeper('com_example/editfoobar') )
		punt_user(null, pines_url('com_example', 'foobar/edit', array('id' => $_REQUEST['id'])));
} else {
	if ( !gatekeeper('com_example/newfoobar') )
		punt_user(null, pines_url('com_example', 'foobar/edit'));
}

$entity = com_example_foobar::factory((int) $_REQUEST['id']);
$entity->print_form();

?>