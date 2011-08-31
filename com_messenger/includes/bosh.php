<?php
/**
 * Sample browser based one-to-one chat application using Jaxl library
 * Usage: Symlink or copy whole Jaxl library folder inside your web folder
 *        Edit "BOSHCHAT_POLL_URL" and "BOSHCHAT_ADMIN_JID" below to suit your environment
 *        Run this app file from the browser e.g. http://path/to/jaxl/app/boshchat.php
 *        View /var/log/jaxl.log for debug info
 *
 * Read more: http://jaxl.net/example/boshchat.php
*/

// Ajax poll url
define('BOSHCHAT_POLL_URL', htmlspecialchars($pines->config->location).'components/com_messenger/includes/jaxl.php');


if(isset($_REQUEST['jaxl'])) { // Valid bosh request
	// Initialize Jaxl Library
	@require_once 'core/jaxl.class.php';
	$jaxl = new JAXL(array(
		'domain'=>'localhost',
		'port'=>5222,
		'boshHost'=>'localhost',
		'authType'=>'DIGEST-MD5',
		'logLevel'=>4
	));

	// User who will receive the message
	define('BOSHCHAT_ADMIN_JID', $_REQUEST['recipient']);

	// Include required XEP's
	$jaxl->requires(array(
		'JAXL0115', // Entity Capabilities
		'JAXL0085', // Chat State Notification
		'JAXL0092', // Software Version
		'JAXL0203', // Delayed Delivery
		'JAXL0202', // Entity Time
		'JAXL0206'  // XMPP over Bosh
	));

	// Sample Bosh chat application class
	class boshchat {

		public static function postAuth($payload, $jaxl) {
			$response = array('jaxl'=>'connected', 'jid'=>$jaxl->jid);
			$jaxl->JAXL0206('out', $response);
		}

		public static function postRosterUpdate($payload, $jaxl) {
			$response = array('jaxl'=>'rosterList', 'roster'=>$jaxl->roster);
			$jaxl->JAXL0206('out', $response);
		}

		public static function postDisconnect($payload, $jaxl) {
			$response = array('jaxl'=>'disconnected');
			$jaxl->JAXL0206('out', $response);
		}

		public static function getMessage($payloads, $jaxl) {
			$html = '';
			foreach($payloads as $payload) {
				// reject offline message
				if($payload['offline'] != JAXL0203::$ns && $payload['type'] == 'chat') {
					if(strlen($payload['body']) > 0) {
						$html .= '<div class="mssgIn">';
						$html .= '<p class="from">'.$payload['from'].'</p>';
						$html .= '<p class="body">'.$payload['body'].'</p>';
						$html .= '</div>';
					}
					else if(isset($payload['chatState']) && in_array($payload['chatState'], JAXL0085::$chatStates)) {
						$html .= '<div class="presIn">';
						$html .= '<p class="from">'.$payload['from'].' chat state '.$payload['chatState'].'</p>';
						$html .= '</div>';
					}
				}
			}

			if($html != '') {
				$response = array('jaxl'=>'message', 'message'=>urlencode($html));
				$jaxl->JAXL0206('out', $response);
			}

			return $payloads;
		}

		public static function getPresence($payloads, $jaxl) {
			$html = '';
			foreach($payloads as $payload) {
				if($payload['type'] == '' || in_array($payload['type'], array('available', 'unavailable'))) {
					$html .= '<div class="presIn">';
					$html .= '<p class="from">'.$payload['from'];
					if($payload['type'] == 'unavailable') $html .= ' is now offline</p>';
					else $html .= ' is now online</p>';
					$html .= '</div>';
				}
			}

			if($html != '') {
				$response = array('jaxl'=>'presence', 'presence'=>urlencode($html));
				$jaxl->JAXL0206('out', $response);
			}

			return $payloads;
		}

		public static function postEmptyBody($body, $jaxl) {
			$response = array('jaxl'=>'pinged');
			$jaxl->JAXL0206('out', $response);
		}

		public static function postAuthFailure($payload, $jaxl) {
			$response = array('jaxl'=>'authFailed');
			$jaxl->JAXL0206('out', $response);
		}

		public static function postCurlErr($payload, $jaxl) {
			if($_REQUEST['jaxl'] == 'disconnect') self::postDisconnect($payload, $jaxl);
			else $jaxl->JAXL0206('out', array('jaxl'=>'curlError', 'code'=>$payload['errno'], 'msg'=>$payload['errmsg']));
		}

	}

	// Add callbacks on various event handlers
	$jaxl->addPlugin('jaxl_post_auth_failure', array('boshchat', 'postAuthFailure'));
	$jaxl->addPlugin('jaxl_post_auth', array('boshchat', 'postAuth'));
	$jaxl->addPlugin('jaxl_post_disconnect', array('boshchat', 'postDisconnect'));
	$jaxl->addPlugin('jaxl_get_empty_body', array('boshchat', 'postEmptyBody'));
	$jaxl->addPlugin('jaxl_get_bosh_curl_error', array('boshchat', 'postCurlErr'));
	$jaxl->addPlugin('jaxl_get_message', array('boshchat', 'getMessage'));
	$jaxl->addPlugin('jaxl_get_presence', array('boshchat', 'getPresence'));
	$jaxl->addPlugin('jaxl_post_roster_update', array('boshchat', 'postRosterUpdate'));

	// Handle incoming bosh request
	switch($_REQUEST['jaxl']) {
		case 'connect':
			$jaxl->user = $_POST['user'];
			$jaxl->pass = $_POST['pass'];
			$jaxl->startCore('bosh');
			break;
		case 'disconnect':
			$jaxl->JAXL0206('endStream');
			break;
		case 'getRosterList':
			$jaxl->getRosterList();
			break;
		case 'setStatus':
			$jaxl->setStatus(FALSE, FALSE, FALSE, TRUE);
			break;
		case 'message':
			$jaxl->sendMessage(BOSHCHAT_ADMIN_JID, $_POST['message']);
			break;
		case 'ping':
			$jaxl->JAXL0206('ping');
			break;
		case 'jaxl':
			$jaxl->JAXL0206('jaxl', $_REQUEST['xml']);
			break;
		default:
			$response = array('jaxl'=>'400', 'desc'=>$_REQUEST['jaxl']." not implemented");
			$jaxl->JAXL0206('out', $response);
			break;
	}
}
if(!isset($_REQUEST['jaxl'])) {
	// Serve application UI if $_REQUEST['jaxl'] is not set
}

?>