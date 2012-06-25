<?php
/**
 * Provide a form to edit a rendition.
 *
 * @package Components\mailer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if (!empty($_REQUEST['id'])) {
	if ( !gatekeeper('com_mailer/editrendition') )
		punt_user(null, pines_url('com_mailer', 'rendition/edit', array('id' => $_REQUEST['id'])));
} else {
	if ( !gatekeeper('com_mailer/newrendition') )
		punt_user(null, pines_url('com_mailer', 'rendition/edit'));
}

$entity = com_mailer_rendition::factory((int) $_REQUEST['id']);
$entity->print_form();

?>