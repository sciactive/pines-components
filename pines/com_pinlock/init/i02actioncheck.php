<?php
/**
 * Check and take over actions.
 *
 * @package Pines
 * @subpackage com_pinlock
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if (!isset($_SESSION['user']) || !isset($_SESSION['user']->pin))
	return;

if (in_array("{$pines->request_component}/{$pines->request_action}", $pines->config->com_pinlock->actions)) {
	if ($_POST['com_pinlock_continue'] == 'true') {
		if ($_POST['pin'] == $_SESSION['user']->pin) {
			$sessionid = $_POST['sessionid'];
			$_POST = $_SESSION[$sessionid]['post'];
			$_GET = $_SESSION[$sessionid]['get'];
			return;
		}
		$pines->page->notice('Incorrect PIN.');
	}
	$pines->com_pinlock->component = $pines->request_component;
	$pines->com_pinlock->action = $pines->request_action;
	$pines->request_component = 'com_pinlock';
	$pines->request_action = 'enterpin';
}

?>