<?php
/**
 * Display the instant messenger.
 *
 * @package Pines
 * @subpackage com_messenger
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');

if (!isset($this->title))
	$this->title = 'Chat';

if (!isset($_SESSION['user']->guid)) {
	echo 'Please log in to chat.';
	return;
}

$pines->icons->load();

$xmpp_user = $_SESSION['user']->username;
$xmpp_pass = $pines->com_messenger->get_temp_secret();

$remote_server = !$pines->config->com_messenger->use_proxy && substr($pines->config->com_messenger->xmpp_bosh_url, 0, 1) !== '/';
?>
<script type="text/javascript">
	<?php if ($remote_server) { ?>
	pines.loadjs("http://flxhr.flensed.com/code/build/flXHR.js");
	<?php } ?>
	pines.loadjs("<?php echo htmlspecialchars($pines->config->location); ?>components/com_messenger/includes/strophejs-latest/strophe.js");
	<?php if ($remote_server) { ?>
	pines.loadjs("<?php echo htmlspecialchars($pines->config->location); ?>components/com_messenger/includes/strophejs-latest/plugins/strophe.flxhr.js");
	<?php } ?>
	pines.loadjs("<?php echo htmlspecialchars($pines->config->location); ?>components/com_messenger/includes/strophejs-latest/plugins/strophe.roster.js");
	pines.loadjs("<?php echo htmlspecialchars($pines->config->location); ?>components/com_messenger/includes/pchat/jquery.pchat.js");
	pines.loadcss("<?php echo htmlspecialchars($pines->config->location); ?>components/com_messenger/includes/pchat/jquery.pchat.default.css");
	pines(function(){
		$("#p_muid_main").pchat({
			pchat_bosh_url: <?php echo json_encode($pines->config->com_messenger->use_proxy ? pines_url('com_messenger', 'xmpp_proxy') : $pines->config->com_messenger->xmpp_bosh_url); ?>,
			pchat_domain: <?php echo json_encode($pines->config->com_messenger->xmpp_server); ?>,
			pchat_jid: <?php echo json_encode($xmpp_user); ?>+"@"+<?php echo json_encode($pines->config->com_messenger->xmpp_server); ?>,
			pchat_password: <?php echo json_encode($xmpp_pass); ?>,
			pchat_widget_box: false,
			//pchat_title: pines.safe("Pines Chat ("+<?php echo json_encode($xmpp_user); ?>+"@"+<?php echo json_encode($pines->config->com_messenger->xmpp_server); ?>+")"),
			pchat_show_log: true
		});
	});
</script>
<div id="p_muid_main"></div>