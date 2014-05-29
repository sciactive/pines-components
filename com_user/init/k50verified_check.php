<?php
/**
 * Check to see if the user is verified, allowing them to resend the link.
 * 
 * If their account is not verified, they can request that the verification link
 * be resent to their email address.
 *
 * @package Components\user
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

// Always include this module for users that are logged in.
// With caching, we have to load a module that uses ajax to check for the secret
// instead of doing that check here.
if (gatekeeper()) {
	$module = new module('com_user', 'resend_verification', 'bottom');
	unset($module);
}
?>