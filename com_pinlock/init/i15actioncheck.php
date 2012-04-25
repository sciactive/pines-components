<?php
/**
 * Check and protect actions.
 *
 * @package Components
 * @subpackage pinlock
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if (!isset($_SESSION['user']) || empty($_SESSION['user']->pin))
	return;

$com_pinlock__request_component = empty($pines->request_component) ? $pines->config->default_component : $pines->request_component;
$com_pinlock__request_action = empty($pines->request_action) ? 'default' : $pines->request_action;
if (!in_array("{$com_pinlock__request_component}/{$com_pinlock__request_action}", $pines->config->com_pinlock->actions)) {
	unset($com_pinlock__request_component, $com_pinlock__request_action);
	return;
}

if ($_POST['com_pinlock_continue'] == 'true') {
	$com_pinlock__sessionid = $_POST['sessionid'];
	if ($_POST['pin'] == $_SESSION['user']->pin) {
		$_POST = unserialize($_SESSION[$com_pinlock__sessionid]['post']);
		$_GET = unserialize($_SESSION[$com_pinlock__sessionid]['get']);
		pines_session('write');
		unset($_SESSION[$com_pinlock__sessionid]);
		pines_session('close');
		$_REQUEST = array_merge((array) $_POST, (array) $_GET);
		unset($com_pinlock__request_component, $com_pinlock__request_action, $com_pinlock__sessionid);
		return;
	}
	if ($pines->config->com_pinlock->allow_switch) {
		$com_pinlock__users = $pines->user_manager->get_users();
		foreach ($com_pinlock__users as $com_pinlock__cur_user) {
			if (empty($com_pinlock__cur_user->pin))
				continue;
			if ($_POST['pin'] == $com_pinlock__cur_user->pin) {
				$_POST = unserialize($_SESSION[$com_pinlock__sessionid]['post']);
				$_GET = unserialize($_SESSION[$com_pinlock__sessionid]['get']);
				pines_log("PIN based user switch from {$_SESSION['user']->username} to {$com_pinlock__cur_user->username}.", 'notice');
				$pines->user_manager->login($com_pinlock__cur_user);
				pines_notice("Logged in as {$com_pinlock__cur_user->username}.");
				unset(
						$com_pinlock__request_component,
						$com_pinlock__request_action,
						$com_pinlock__sessionid,
						$com_pinlock__cur_user,
						$com_pinlock__users
					);
				return;
			}
		}
		unset($com_pinlock__cur_user);
		unset($com_pinlock__users);
	}
	$pines->com_pinlock->sessionid = $com_pinlock__sessionid;
	pines_session('write');
	$_SESSION['com_pinlock__attempts']++;
	pines_session('close');
	if ($_SESSION['com_pinlock__attempts'] >= $pines->config->com_pinlock->max_tries) {
		pines_log('Maximum failed login attempts reached.', 'warning');
		$pines->user_manager->logout();
		punt_user('Maximum failed login attempts reached.', pines_url());
	}
	pines_notice('Incorrect PIN.');
	unset($com_pinlock__sessionid);
} else {
	$pines->com_pinlock->sessionid = 'com_pinlock'.rand(0, 1000000000);
	pines_session('write');
	$_SESSION['com_pinlock__attempts'] = 0;
	$_SESSION[$pines->com_pinlock->sessionid] = array(
		'post' => serialize($_POST),
		'get' => serialize($_GET)
	);
	pines_session('close');
}
$pines->com_pinlock->component = $com_pinlock__request_component;
$pines->com_pinlock->action = $com_pinlock__request_action;
$pines->request_component = 'com_pinlock';
$pines->request_action = 'enterpin';

?>