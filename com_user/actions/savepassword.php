<?php
/**
 * Save the current user's new password.
 *
 * @package Components\user
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_user/self') )
	punt_user(null, pines_url());

if (empty($_REQUEST['new1']) || $_REQUEST['new1'] != $_REQUEST['new2'] || !$_SESSION['user']->check_password($_REQUEST['current'])) {
	pines_notice('Invalid password submitted.');
	$_SESSION['user']->print_form_password();
	return;
}

pines_session('write');
$_SESSION['user']->password($_REQUEST['new1']);
if ($_SESSION['user']->save())
	pines_notice('Changed password successfully.');
else
	pines_error('Error saving new password.');
pines_session('close');

pines_redirect(pines_url('com_user', 'editself'));

?>