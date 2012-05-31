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

if (!isset($this->widget_title))
	$this->widget_title = 'Chat #name# [#username#]';
if (!empty($this->widget_title))
	$this->title = htmlspecialchars(str_replace(array('#name#', '#username#'), array($_SESSION['user']->name, $_SESSION['user']->username), $this->widget_title));

if (!isset($this->interface))
	$this->interface = 'inline';

$this->roster_max_len = isset($this->roster_max_len) ? (int) $this->roster_max_len : 20;

if ($this->guest != 'true' && !isset($_SESSION['user']->guid)) {
	echo 'Please log in to chat.';
	return;
}

$pines->com_messenger->load();

if ($this->guest == 'true') {
	$guest = $pines->com_messenger->get_guest();
	$xmpp_user = $guest->username;
	$xmpp_pass = $guest->password;
} else {
	$xmpp_user = $_SESSION['user']->username;
	$xmpp_pass = $pines->com_messenger->get_temp_secret();
}

if ($this->interface == 'floating') {
	// Add a class to remove the module frame.
	$this->classes[] = 'hide';
	$frame_class = uniqid('frame_');
	$this->classes[] = $frame_class;
}
?>
<script type="text/javascript">
	pines(function(){
		<?php if ($this->status_url == 'true') { ?>
		var status = <?php echo json_encode('URL: '.$_SERVER['REQUEST_URI']); ?>;
		localStorage.setItem("pchat-presence-status", status);
		<?php } ?>
		var pchat = $("#p_muid_main").pchat({
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
			<?php if ($this->status_url == 'true') { ?>
			pchat_onconnect: function(){
				setTimeout(function(c){
					var pres = localStorage.getItem("pchat-presence");
					if (!pres)
						pres = "available";
					c.pchat_set_presence(pres, status);
				}, 500, this);
			},
			<?php } ?>
			//pchat_show_log: true,
			<?php if ($this->interface == 'floating') { ?>
			pchat_title: pines.safe(<?php echo json_encode($this->title); ?>),
			<?php } elseif ($this->interface == 'inline') { ?>
			pchat_interface_container: false,
			pchat_widget_box: false,
			pchat_title: false,
			<?php } ?>
			pchat_status_input: <?php echo json_encode($this->hide_status_box != 'true'); ?>,
			pchat_roster_max_len: <?php echo json_encode($this->roster_max_len); ?>
		});
		<?php if ($this->interface == 'floating') { ?>
		// Remove the module frame.
		$(<?php echo json_encode(".$frame_class"); ?>).remove();
		<?php } ?>
		/*var subscribe = pchat.get(0).pines_chat.pchat_connection.roster.subscribe;
		pchat.get(0).pines_chat.pchat_connection.roster.subscribe = function(){
			alert('You hit subscribe! You win!');
			subscribe.apply(this, arguments);
		};*/
	});
</script>
<div id="p_muid_main"></div>