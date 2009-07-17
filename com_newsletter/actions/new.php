<?php
/**
 * Create a newsletter.
 *
 * @package XROOM
 * @subpackage com_newsletter
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('X_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_newsletter/managemails') ) {
	$config->user_manager->punt_user("You don't have necessary permission.", $config->template->url('com_newsletter', 'list', null, false));
	return;
}

$config->com_newsletter->edit_mail("New mail.", NULL, 'com_newsletter', 'edit');
?>
