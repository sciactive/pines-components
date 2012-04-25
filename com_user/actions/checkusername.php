<?php
/**
 * Determine if a username is available.
 *
 * @package Components
 * @subpackage user
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if (!$pines->config->com_user->check_username)
	throw new HttpClientException(null, 404);

$pines->page->override = true;
header('Content-Type: application/json');

if (!empty($_REQUEST['id']))
	$id = intval($_REQUEST['id']);

if (empty($_REQUEST['username'])) {
	$pines->page->override_doc(json_encode(array('result' => false, 'message' => 'Please specify a username.')));
	return;
}
if ($pines->config->com_user->max_username_length > 0 && strlen($_REQUEST['username']) > $pines->config->com_user->max_username_length) {
	$pines->page->override_doc(json_encode(array('result' => false, 'message' => "Usernames must not exceed {$pines->config->com_user->max_username_length} characters.")));
	return;
}
if (array_diff(str_split($_REQUEST['username']), str_split($pines->config->com_user->valid_chars))) {
	$pines->page->override_doc(json_encode(array('result' => false, 'message' => $pines->config->com_user->valid_chars_notice)));
	return;
}
if (!preg_match($pines->config->com_user->valid_regex, $_REQUEST['username'])) {
	$pines->page->override_doc(json_encode(array('result' => false, 'message' => $pines->config->com_user->valid_regex_notice)));
	return;
}
$test = user::factory($_REQUEST['username']);
if (isset($test->guid) && (!isset($id) || $id <= 0 || $test->guid != $id)) {
	$pines->page->override_doc(json_encode(array('result' => false, 'message' => 'That username is taken.')));
	return;
}

$pines->page->override_doc(json_encode(array('result' => true, 'message' => (isset($id) ? 'Username is valid.' : 'Username is available!'))));

?>