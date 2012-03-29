<?php
/**
 * com_messenger class.
 *
 * @package Pines
 * @subpackage com_messenger
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

/**
 * com_messenger main class.
 *
 * @package Pines
 * @subpackage com_messenger
 */
class com_messenger extends component {
	/**
	 * Make a temporary secret to use as a password for current user XMPP login.
	 * @return string The secret password.
	 */
	function get_temp_secret() {
		if (!isset($_SESSION['user']->guid))
			return '';
		pines_session('write');
		$_SESSION['user']->xmpp_secret = uniqid('xmpp_secret_');
		$_SESSION['user']->xmpp_secret_expire = strtotime('+5 minutes');
		$_SESSION['user']->save();
		pines_session('close');
		return $_SESSION['user']->xmpp_secret;
	}
}

?>