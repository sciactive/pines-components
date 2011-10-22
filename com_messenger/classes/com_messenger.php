<?php
/**
 * com_messenger class.
 *
 * @package Pines
 * @subpackage com_messenger
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
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
	 * Whether the messenger JavaScript has been loaded.
	 * @access private
	 * @var bool $js_loaded
	 */
	private $js_loaded = false;

	/**
	 * Load the JavaScript.
	 *
	 * This will place the required scripts into the document's head section.
	 */
	function load() {
		if (!$this->js_loaded) {
			$module = new module('com_messenger', 'messenger', 'head');
			$module->render();
			$this->js_loaded = true;
		}
	}

	/**
	 * Create a new XMPP account
	 *
	 * This will create a new xmpp user.
	 *
	function create_user() {
		$username = $_SESSION['user']->username;
		$password = $_SESSION['user']->password;
		$hostname = $pines->config->com_messenger->xmpp_server;
		// This does not work!
		exec('sudo ejabberdctl register '.$username.' '.$hostname.' '.$password, $output, $status);
		if($output == 0) {
			pines_notice('Success!');
		} else {
			// Failure, $output has the details
			echo '<pre>';
			foreach($output as $o) {
				echo $o."\n";
			}
			echo '</pre>';
		}
	}
	*/
}

?>