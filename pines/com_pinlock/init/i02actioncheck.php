<?php
/**
 * Check and protect actions.
 *
 * @package Pines
 * @subpackage com_pinlock
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if (!isset($_SESSION['user']) || empty($_SESSION['user']->pin))
	return;

if (!in_array("{$pines->request_component}/{$pines->request_action}", $pines->config->com_pinlock->actions))
	return;

if ($_POST['com_pinlock_continue'] == 'true') {
	$com_pinlock__sessionid = $_POST['sessionid'];
	if ($_POST['pin'] == $_SESSION['user']->pin) {
		$_POST = unserialize($_SESSION[$com_pinlock__sessionid]['post']);
		$_GET = unserialize($_SESSION[$com_pinlock__sessionid]['get']);
		unset($_SESSION[$com_pinlock__sessionid]);
		return;
	}
	$pines->com_pinlock->sessionid = $com_pinlock__sessionid;
	$pines->page->notice('Incorrect PIN.');
} else {
	$pines->com_pinlock->sessionid = 'com_pinlock'.rand(0, 1000000000);
	$_SESSION[$pines->com_pinlock->sessionid] = array(
		'post' => serialize($_POST),
		'get' => serialize($_GET)
	);
}
$pines->com_pinlock->component = $pines->request_component;
$pines->com_pinlock->action = $pines->request_action;
$pines->request_component = 'com_pinlock';
$pines->request_action = 'enterpin';

?>