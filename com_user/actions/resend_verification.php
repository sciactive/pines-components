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

if (!$pines->config->com_user->confirm_email || !gatekeeper() || !(isset($_SESSION['user']->secret) || isset($_SESSION['user']->new_email_secret)) ) {
	$pines->page->override_doc('false');
	return;
}

// User might have the secret or the new_email_secret
if (isset($_SESSION['user']->new_email_secret)) {
	if ($_SESSION['user']->send_changed_email_verification())
		$pines->page->override_doc('true');
	else
		$pines->page->override_doc('false');
} else if (isset($_SESSION['user']->secret)) {
	if ($_SESSION['user']->send_email_verification())
		$pines->page->override_doc('true');
	else
		$pines->page->override_doc('false');
} else
	$pines->page->override_doc('false');

?>