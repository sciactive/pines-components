<?php
/**
 * Provide a form to edit a thread.
 *
 * @package Pines
 * @subpackage com_notes
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_notes/editthread') )
	punt_user(null, pines_url('com_notes', 'thread/edit', array('id' => $_REQUEST['id'])));

$entity = com_notes_thread::factory((int) $_REQUEST['id']);
if (!isset($entity->guid))
	throw new HttpClientException(null, 404);
$entity->print_form();

?>