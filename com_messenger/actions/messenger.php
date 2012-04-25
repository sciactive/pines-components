<?php
/**
 * Handle messages and messaging events.
 *
 * @package Components\messenger
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 * @todo Determine if this file is still useful.
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

$pines->page->override = true;
header('Content-Type: application/json');

$close = ($_REQUEST['close'] == 'true') ? true : false;
$xmpp_id = $_REQUEST['xmpp_id'];
$message = $_REQUEST['message'];

pines_session('write');
if (!isset($_SESSION['chats']))
	$_SESSION['chats'] = array();

if ($close) {
	unset($_SESSION['chats'][$xmpp_id]);
} else {
	if (!isset($_SESSION['chats'][$xmpp_id]))
		$_SESSION['chats'][$xmpp_id] = array();

	$_SESSION['chats'][$xmpp_id][] = $message;
}
pines_session('close');

$pines->page->override_doc(json_encode(true));

?>