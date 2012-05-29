<?php
/**
 * Display the instant messenger.
 *
 * @package Components\messenger
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');

$this->title = 'Chat ('.htmlspecialchars($_SESSION['user']->username).')';

if (!isset($_SESSION['user']->guid)) {
	echo 'Please log in to chat.';
	return;
}

$pines->com_messenger->load();

$xmpp_user = $_SESSION['user']->username;
$xmpp_pass = $pines->com_messenger->get_temp_secret();

// Add a class to remove the module frame.
$this->classes[] = 'hide';
$frame_class = uniqid('frame_');
$this->classes[] = $frame_class;
?>
<script type="text/javascript">
	pines(function(){
		$("#p_muid_main").pchat({
			pchat_bosh_url: <?php echo json_encode($pines->config->com_messenger->use_proxy ? pines_url('com_messenger', 'xmpp_proxy') : $pines->config->com_messenger->xmpp_bosh_url); ?>,
			pchat_domain: <?php echo json_encode($pines->config->com_messenger->xmpp_server); ?>,
			pchat_jid: <?php echo json_encode($xmpp_user); ?>+"@"+<?php echo json_encode($pines->config->com_messenger->xmpp_server); ?>,
			pchat_password: <?php echo json_encode($xmpp_pass); ?>,
			pchat_sound: true,
			pchat_sounds: {
				offline: ["<?php echo htmlspecialchars($pines->config->location); ?>components/com_messenger/includes/pchat/sounds/offline.ogg", "<?php echo htmlspecialchars($pines->config->location); ?>components/com_messenger/includes/pchat/sounds/offline.mp3"],
				online: ["<?php echo htmlspecialchars($pines->config->location); ?>components/com_messenger/includes/pchat/sounds/online.ogg", "<?php echo htmlspecialchars($pines->config->location); ?>components/com_messenger/includes/pchat/sounds/online.mp3"],
				received: ["<?php echo htmlspecialchars($pines->config->location); ?>components/com_messenger/includes/pchat/sounds/received.ogg", "<?php echo htmlspecialchars($pines->config->location); ?>components/com_messenger/includes/pchat/sounds/received.mp3"]
			},
			//pchat_show_log: true,
			//pchat_title: pines.safe("Chat ("+<?php echo json_encode($xmpp_user); ?>+")"),
			pchat_status_input: true,
			// Keep the interface as a dashboard widget.
			pchat_interface_container: false,
			pchat_widget_box: false,
			pchat_title: false
		});
		// Remove the module frame.
		$(<?php echo json_encode(".$frame_class"); ?>).remove();
	});
</script>
<div id="p_muid_main"></div>