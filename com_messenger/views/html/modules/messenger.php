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

$users = $pines->com_user->get_users();
foreach ($users as $key => &$cur_user) {
	if ( $cur_user->is($_SESSION['user']) ||
		(!$cur_user->in_group($user_group) && !$cur_user->is_descendant($user_group)) )
		unset($users[$key]);
}
unset($cur_user);
?>
<style type="text/css" >
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
	#p_muid_chat .chat_tabs {
		font-size: 7pt;
	}
	#p_muid_chat .chat_tab {
		margin: 0;
		padding: 0;
	}
	#p_muid_chat .chat_tab ul {
		padding: 0 1.4em;
		white-space: nowrap;
		list-style: circle;
		font-size: 9pt;
	}
	#p_muid_chat .chat_tab li a:hover {
		text-decoration: underline;
	}
	#p_muid_chat .chat_tab ul li a {
		text-decoration: none;
	}
	#p_muid_chat .close_btn {
		border: 0;
		display: inline-block;
		width: 16px;
		height: 16px;
		position: absolute;
		top: 10%;
		left: 85%;
	}
</style>
<script type="text/javascript">
	pines.com_messenger_chat = function(user_name, user_jid){
		var current_tab = '#p_muid_tab_'+user_jid;
		// Create the new chat window if it doesn't exist.
		if ($(current_tab+" .write").val() == undefined) {
			var skel = $("#p_muid_skeleton").html();
			// Make a new chat window.
			$("#p_muid_conversations").append(skel.replace(/xmpp_jid/g, user_jid));
			// Add a tab for the new chat window.
			$("#p_muid_chat").tabs('add', current_tab, user_name);
		}
		// Open the chat window for the given user.
		var tab_index = parseInt($(current_tab).index() - 3);
		$('#p_muid_chat').tabs('select', tab_index);
		$(current_tab+" .write").focus();
	};

	pines.com_messenger_close = function(user_jid){
		// Get the index of the chat window for the given user.
		var tab_index = parseInt($('#p_muid_tab_'+user_jid).index() - 3);
		// Remove the chat window.
		$('#p_muid_tab_'+user_jid).remove();
		// Remove the tab for the chat window.
		$('#p_muid_chat').tabs('remove', tab_index);

		$.ajax({
			url: <?php echo json_encode(pines_url('com_messenger', 'messenger')); ?>,
			type: "POST",
			dataType: "json",
			data: {
				"close": true,
				"xmpp_id": user_jid
			}
		});
	};

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
					// Receive a message from a user.
					// Remove the hostname and temporary user jabberid from the author name;
					var jabber_id = payload.message.replace(/%40.*/, '').replace(/.*%3E/, '');
					pines.com_messenger_chat(jabber_id, jabber_id);
					boshchat.appendMessage(jabber_id, jaxl.urldecode(payload.message), true);
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
			appendMessage: function(jid, message, new_message) {
				var chat_box = $('#p_muid_tab_'+jid+' .read');
				chat_box.append(pines.safe(message.replace(/@.*?</, '<')));
				if (new_message)
					chat_box.animate({ scrollTop: chat_box.prop('scrollHeight') }, 300).effect('highlight');
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
	});

	pines(function(){

		$("#p_muid_chat").tabs();
		$("#p_muid_chat .chat_tabs").sortable();

		jaxl.payloadHandler = new Array('boshchat', 'payloadHandler');

		// Connect and login to the xmpp server.
		obj = new Object;
		// TODO create xmpp users and passwords for users when they are created.
		obj['user'] = '<?php echo htmlspecialchars($xmpp_user); ?>';
		obj['pass'] = 'password'; //'<?php // echo htmlspecialchars($_SESSION['user']->password); ?>';

		jaxl.connect(obj);

		$("#p_muid_chat").delegate(".write", "focus", function(){
			$(this).removeClass('ui-widget-content');
			$(this).val('');
		}).delegate(".write", "blur", function(){
			$(this).addClass('ui-widget-content');
			if($(this).val() == '') $(this).val('Type your message');
		}).delegate(".write", "keydown", function(e){
			if(e.keyCode == 13 && jaxl.connected) {
				message = $.trim($(this).val());
				if(message.length == 0) return false;
				$(this).val('');

				var recipient = $(this).attr('title')+'@<?php echo htmlspecialchars($pines->config->com_messenger->xmpp_server); ?>';
				boshchat.appendMessage(recipient.replace(/@.*/, ''), boshchat.prepareMessage(jaxl.jid, message), true);
				// Send a message to a user.
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
				$jabber_id = key($chat_log); ?>
				pines.com_messenger_chat(<?php echo json_encode(htmlspecialchars($jabber_id)); ?>, <?php echo json_encode(htmlspecialchars($jabber_id)); ?>);
			<?php foreach ($cur_chat as $cur_msg) { ?>
				boshchat.appendMessage(<?php echo json_encode(htmlspecialchars($jabber_id)); ?>, <?php echo json_encode(htmlspecialchars($cur_msg)); ?>);
			<?php } ?>
				$('#p_muid_tab_<?php echo addslashes(htmlspecialchars($jabber_id)); ?> .read').animate({ scrollTop: $('#p_muid_tab_<?php echo addslashes(htmlspecialchars($jabber_id)); ?> .read').prop('scrollHeight') }, 300);
			<?php }
		} ?>
	});
</script>
<form id="p_muid_chat" class="pf-form" style="clear: both;" onsubmit="return false;">
	<ul class="chat_tabs">
		<li><a href="#p_muid_tab_users">Users</a></li>
	</ul>
	<div id="p_muid_conversations">
		<div id="p_muid_tab_users" class="pf-form chat_tab">
			<div class="pf-element">
				<ul>
					<?php foreach ($users as $cur_user) { ?>
					<li><a href="#" onclick="pines.com_messenger_chat(<?php echo json_encode(htmlspecialchars($cur_user->name_first)); ?>, <?php echo json_encode(htmlspecialchars($cur_user->username)); ?>);"><?php echo htmlspecialchars($cur_user->name); ?></a></li>
					<?php } ?>
				</ul>
			</div>
			<br class="pf-clearing" />
		</div>
	</div>
	<div id="p_muid_skeleton" style="display: none;">
		<div id="p_muid_tab_xmpp_jid" class="chat_tab">
			<a href="#" class="close_btn picon picon-edit-delete" title="Close" onclick="pines.com_messenger_close('xmpp_jid');"></a>
			<div class="read"></div>
			<input type="text" value="Type your message" class="write ui-widget-content" title="xmpp_jid">
		</div>
	</div>
	<br class="pf-clearing" />
</form>