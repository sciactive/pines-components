<?php
/**
 * Resend a verification email.
 *
 * @package Components\user
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

$pines->page->override = true;
header('Content-Type: application/json');

if (!$pines->config->com_user->confirm_email || !gatekeeper() || !isset($_SESSION['user']->secret)) {
	$pines->page->override_doc('false');
	return;
}

// Send the verification email.
if ($_SESSION['user']->send_email_verification())
	$pines->page->override_doc('true');
else
	$pines->page->override_doc('false');

?>