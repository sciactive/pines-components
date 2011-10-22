<?php
/**
 * Display the history of an ESP.
 *
 * @package Pines
 * @subpackage com_esp
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_esp/listplans') )
	punt_user(null, pines_url('com_esp', 'history', array('id' => $_REQUEST['id'])));

$entity = com_esp_plan::factory((int) $_REQUEST['id']);
if (!isset($entity->guid)) {
	pines_error('Requested ESP id is not accessible.');
	return;
}
if (isset($_REQUEST['history_note'])){
	$entity->history[] = array('date' => time(), 'note' => htmlspecialchars($_REQUEST['history_note']), 'user' => $_SESSION['user']);
	$entity->save();
}
$entity->history();

?>