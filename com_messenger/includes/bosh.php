<?php
/*
 * Example bosh chat application using Jaxl library
 * Read more: http://bit.ly/aMozMy
 */

// Ajax poll url
define('BOSHCHAT_POLL_URL', htmlspecialchars($pines->config->location).'components/com_messenger/includes/jaxl.php');

// User who will receive the message
define('BOSHCHAT_ADMIN_JID', $_REQUEST['recipient']);

if(isset($_REQUEST['jaxl'])) { // Valid bosh request
	// Initialize Jaxl Library
	$jaxl = new JAXL();

	// Include required XEP's
	jaxl_require(array(
		'JAXL0115', // Entity Capabilities
		'JAXL0085', // Chat State Notification
		'JAXL0092', // Software Version
		'JAXL0203', // Delayed Delivery
		'JAXL0202', // Entity Time
		'JAXL0206'  // XMPP over Bosh
	), $jaxl);

	// Sample Bosh chat application class
	class boshchat {

		public static function doAuth($mechanism) {
			global $jaxl;
			$jaxl->auth("DIGEST-MD5");
		}

		public static function postAuth() {
			global $jaxl;
			$response = array('jaxl' => 'connected', 'jid' => $jaxl->jid);
			$jaxl->JAXL0206('out', $response);
		}

		public static function handleRosterList($payload) {
			global $jaxl;

			$roster = array();
			if(is_array($payload['queryItemJid'])) {
				foreach($payload['queryItemJid'] as $key => $jid) {
					$roster[$jid]['group'] = $payload['queryItemGrp'][$key];
					$roster[$jid]['subscription'] = $payload['queryItemSub'][$key];
					$roster[$jid]['name'] = $payload['queryItemName'][$key];
				}
			}

			$response = array('jaxl' => 'rosterList', 'roster' => $roster);
			$jaxl->JAXL0206('out', $response);
		}

		public static function postDisconnect() {
			global $jaxl;
			$response = array('jaxl'=>'disconnected');
			$jaxl->JAXL0206('out', $response);
		}

		public static function getMessage($payloads) {
			global $jaxl;

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
				$response = array('jaxl' => 'message', 'message' => urlencode($html));
				$jaxl->JAXL0206('out', $response);
			}

			return $payloads;
		}

		public static function getPresence($payloads) {
			global $jaxl;

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
				$response = array('jaxl' => 'presence', 'presence' => urlencode($html));
				$jaxl->JAXL0206('out', $response);
			}

			return $payloads;
		}

		public static function postEmptyBody($body) {
			global $jaxl;
			$response = array('jaxl' => 'pinged');
			$jaxl->JAXL0206('out', $response);
		}

		public static function postAuthFailure() {
			global $jaxl;
			$response = array('jaxl' => 'authFailed');
			$jaxl->JAXL0206('out', $response);
		}

	}

	// Add callbacks on various event handlers
	JAXLPlugin::add('jaxl_post_auth_failure', array('boshchat', 'postAuthFailure'));
	JAXLPlugin::add('jaxl_post_auth', array('boshchat', 'postAuth'));
	JAXLPlugin::add('jaxl_post_disconnect', array('boshchat', 'postDisconnect'));
	JAXLPlugin::add('jaxl_get_auth_mech', array('boshchat', 'doAuth'));
	JAXLPlugin::add('jaxl_get_empty_body', array('boshchat', 'postEmptyBody'));
	JAXLPlugin::add('jaxl_get_message', array('boshchat', 'getMessage'));
	JAXLPlugin::add('jaxl_get_presence', array('boshchat', 'getPresence'));

	// Handle incoming bosh request
	switch($jaxl->action) {
		case 'connect':
			$jaxl->user = $_POST['user'];
			$jaxl->pass = $_POST['pass'];
			$jaxl->JAXL0206('startStream', $jaxl->host, $jaxl->port, $jaxl->domain);
			break;
		case 'disconnect':
			$jaxl->JAXL0206('endStream');
			break;
		case 'getRosterList':
			$jaxl->getRosterList(array('boshchat', 'handleRosterList'));
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
			$response = array('jaxl' => '400', 'desc' => $jaxl->action." not implemented");
			$jaxl->JAXL0206('out', $response);
			break;
	}
}
if(!isset($_REQUEST['jaxl'])) { // Serve application UI if $_REQUEST['jaxl'] is not set

}

?>