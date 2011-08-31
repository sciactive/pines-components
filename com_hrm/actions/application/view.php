<?php
/**
 * View an employment application.
 *
 * @package Pines
 * @subpackage com_hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if (!gatekeeper('com_hrm/listapplications'))
	punt_user(null, pines_url('com_hrm', 'application/view', array('id' => $_REQUEST['id'])));

$entity = com_hrm_application::factory((int) $_REQUEST['id']);
if (!isset($entity->guid)) {
	punt_user(null, pines_url('com_hrm', 'application/list'));
	pines_error('The application with the specified ID could not be retrieved.');
	return;
}

$entity->view_application();

?>