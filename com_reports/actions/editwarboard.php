<?php
/**
 * Provide a form to edit the company warboard.
 *
 * @package Pines
 * @subpackage com_reports
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_reports/editwarboard'))
	punt_user(null, pines_url('com_reports', 'editwarboard'));

$warboard = $pines->entity_manager->get_entity(array('class' => com_reports_warboard), array('&', 'tag' => array('com_reports', 'warboard')));

if (!isset($warboard->guid)) {
	$warboard = com_reports_warboard::factory();
	$warboard->save();
}

$warboard->print_form();

?>