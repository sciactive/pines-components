<?php
/**
 * Provide a form to apply for employement.
 *
 * @package Components
 * @subpackage hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if (!gatekeeper())
	punt_user(null, pines_url('com_hrm', 'application/edit', array('id' => $_REQUEST['id'])));

$entity = com_hrm_application::factory((int) $_REQUEST['id']);
if (!gatekeeper('com_hrm/editapplication') &&
	isset($entity->guid) && !$entity->user->is($_SESSION['user'])) {
	punt_user(null, pines_url('com_hrm', 'application/edit'));
	pines_error('You do not have permission to edit this application.');
	return;
}

$entity->print_form();

?>