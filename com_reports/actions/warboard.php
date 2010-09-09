<?php
/**
 * Show the company warboard.
 *
 * @package Pines
 * @subpackage com_reports
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_reports/warboard') )
	punt_user(null, pines_url('com_reports', 'warboard'));

$warboard = $pines->entity_manager->get_entity(array('class' => com_reports_warboard), array('&', 'tag' => array('com_reports', 'warboard')));

if (!isset($warboard->guid)) {
	$warboard = com_reports_warboard::factory();
	$warboard->save();
}

$warboard->show();

?>