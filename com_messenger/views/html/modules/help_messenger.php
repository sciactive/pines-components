<?php
/**
 * Display the instant messenger.
 *
 * @package Pines
 * @subpackage com_messenger
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');

$pines->com_messenger->load();

$xmpp_user = $_SESSION['user']->username;
$user_group = $_SESSION['user']->group;
$chat_log = $_SESSION['chats'];

?>
<style type="text/css">
	#p_muid_chat body {
		font: 62.5% "lucida grande", "lucida sans unicode", helvetica, arial, sans-serif;
	}
	#p_muid_chat input {
		width: 100%;
	}
	#p_muid_chat label, #p_muid_chat input {
		margin-bottom: 5px;
	}
	#p_muid_chat  .read {
		width: 100%;
		height: 250px;
		overflow-x: hidden;
		overflow-y: auto;
		border: 1px solid #E7E7E7;
		display: none;
	}
	#p_muid_chat .read .mssgIn, .read .presIn {
		text-align: left;
		margin: 5px;
		padding: 0px 5px;
		border-bottom: 1px solid #EEE;
	}
	#p_muid_chat .read .presIn {
		font-size: 11px;
		font-weight: normal;
	}
	#p_muid_chat .read .mssgIn p.from, #p_muid_chat .read .presIn p.from {
		padding: 0px;
		margin: 0px;
		font-size: 13px;
	}
	#p_muid_chat .read .mssgIn p.from {
		font-weight: bold;
	}
	#p_muid_chat .read .mssgIn p.body {
		padding: 0px;
		margin: 0px;
		font-size: 12px;
	}
	#p_muid_chat .write {
		width: 98%;
		border: 1px solid;
		height: 20px;
		padding: 1px;
		font-size: 13px;
		display: none;
	}
</style>
<script type="text/javascript">
	pines(function(){
		jaxl.pollUrl = "<?php echo htmlspecialchars($pines->config->location); ?>components/com_messenger/includes/bosh.php";
		boshchat = {
			payloadHandler: function(payload) {
				if(payload.jaxl == 'authFailed')
					jaxl.connected = false;
				else if(payload.jaxl == 'connected') {
						jaxl.connected = true;
						jaxl.jid = payload.jid;

						$('#p_muid_chat .read').css('display', 'block');
						$('#p_muid_chat .write').css('display', 'block');

						obj = new Object;
						obj['jaxl'] = 'getRosterList';
						jaxl.sendPayload(obj);
				}
				else if(payload.jaxl == 'rosterList') {
						obj = new Object;
						obj['jaxl'] = 'setStatus';
						jaxl.sendPayload(obj);
				}
				else if(payload.jaxl == 'disconnected') {
						jaxl.connected = false;
						jaxl.disconnecting = false;

						$('#p_muid_chat .read').css('display', 'none');
						$('#p_muid_chat .write').css('display', 'none');

						console.log('disconnected');
				}
				else if(payload.jaxl == 'message') {
					var jabber_id = payload.message.replace(/%40.*/, '').replace(/.*%3E/, '');
					boshchat.appendMessage(jaxl.urldecode(payload.message), true);
					jaxl.ping();

					$.ajax({
						url: <?php echo json_encode(pines_url('com_messenger', 'messenger')); ?>,
						type: "POST",
						dataType: "json",
						data: {
							"close": false,
							"xmpp_id": jabber_id,
							"message": jaxl.urldecode(payload.message)
						}
					});
				}
				else if(payload.jaxl == 'presence') {
					jaxl.ping();
				}
				else if(payload.jaxl == 'pinged') {
					jaxl.ping();
				}
			},
			appendMessage: function(message, new_message) {
				var chat_box = $('#p_muid_chat .read');
				chat_box.append(pines.safe(message.replace(/@.*?</, '<')));
				if (new_message)
					chat_box.animate({ scrollTop: $('#p_muid_chat .read').prop('scrollHeight') }, 300).effect('highlight');
			},
			prepareMessage: function(jid, message) {
				html = '';
				html += '<div class="mssgIn">';
				html += '<p class="from">'+jid+'</p>';
				html += '<p class="body">'+message+'</div>';
				html += '</div>';
				return html;
			}
		};


		jaxl.payloadHandler = new Array('boshchat', 'payloadHandler');

		// Connect and login to the xmpp server.
		obj = new Object;
		// TODO create xmpp users and passwords for users when they are created.
		obj['user'] = <?php echo json_encode($xmpp_user); ?>;
		obj['pass'] = 'password';

		jaxl.connect(obj);

		$('#p_muid_chat .write').focus(function() {
			$(this).removeClass('ui-widget-content');
			$(this).val('');
		});

		$('#p_muid_chat .write').blur(function() {
			$(this).addClass('ui-widget-content');
			if($(this).val() == '') $(this).val('Type your message');
		});

		$('#p_muid_chat .write').keydown(function(e) {
			if(e.keyCode == 13 && jaxl.connected) {
				message = $.trim($(this).val());
				if(message.length == 0) return false;
				$(this).val('');

				var recipient = <?php echo json_encode($pines->config->com_messenger->xmpp_support_user); ?>+<?php echo json_encode('@'.$pines->config->com_messenger->xmpp_server); ?>;
				boshchat.appendMessage(boshchat.prepareMessage(<?php echo json_encode($xmpp_user); ?>, message), true);

				obj = new Object;
				obj['recipient'] = recipient;
				obj['jaxl'] = 'message';
				obj['message'] = message;
				jaxl.sendPayload(obj);

				$.ajax({
					url: <?php echo json_encode(pines_url('com_messenger', 'messenger')); ?>,
					type: "POST",
					dataType: "json",
					data: {
						"close": false,
						"xmpp_id": recipient.replace(/@.*/, ''),
						"message": boshchat.prepareMessage(jaxl.jid, message)
					}
				});
			}
		});
		// Load all existing conversations.
		<?php if (!empty($chat_log)) {
			foreach ($chat_log as $cur_chat) {
				$jabber_id = key($chat_log);
				foreach ($cur_chat as $cur_msg) { ?>
					boshchat.appendMessage(<?php echo json_encode(htmlspecialchars($cur_msg)); ?>);
			<?php }
			} ?>
			$('#p_muid_chat .read').animate({ scrollTop: $('#p_muid_chat .read').prop('scrollHeight') }, 300);
		<?php } ?>
	});
</script>
<form id="p_muid_chat" class="pf-form" style="clear: both;" onsubmit="return false;">
	<div class="read"></div>
	<input type="text" value="Type your message" class="write ui-widget-content">
</form>