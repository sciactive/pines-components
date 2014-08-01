<?php
/**
 * A view to load GAE HTML
 *
 * @package Components\gae_chat
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Mohammed Ahmed <mohammedsadikahmed@gmail.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
?>

<?php $employee = ($_SESSION['user']->employee && gatekeeper('com_gae_chat/employeechat')); 
	$dark = (bool) $pines->config->com_gae_chat->dark_theme;
?>
<div id="gae-chat-variables" class="hide">
    <div id="send_message_url" class="hide" data-url="<?php echo $pines->config->com_gae_chat->message_url; ?>"></div>
    <div id="get_token_url" class="hide" data-url="<?php echo $pines->config->com_gae_chat->pines_token_url;?>"></div>
    <div id="online_test_url" class="hide" data-url="<?php echo $pines->config->com_gae_chat->ping_test_url;?>"></div>
    <div id="send_online_check_url" class="hide" data-url="<?php echo $pines->config->com_gae_chat->online_check_url;?>"></div>
    <div id="get_messages_url" class="hide" data-url="<?php echo $pines->config->com_gae_chat->get_messages_url;?>"></div>
    <div id="get_customer_messages_url" class="hide" data-url="<?php echo $pines->config->com_gae_chat->get_customer_messages_url;?>"></div>
    <div id="get_users_and_messages_url" class="hide" data-url="<?php echo $pines->config->com_gae_chat->get_users_and_messages_url;?>"></div>
    <div id="chat_customer_pic" class="hide" data-url="<?php echo $pines->config->com_gae_chat->customer_pic;?>"></div>
    <div id="chat_employee_pic" class="hide" data-url="<?php echo $pines->config->com_gae_chat->employee_pic;?>"></div>
    <div id="ping_all_customers_url" class="hide" data-url="<?php echo $pines->config->com_gae_chat->ping_all_customers_url;?>"></div>
    <div id="refresh_online_users_url" class="hide" data-url="<?php echo $pines->config->com_gae_chat->refresh_online_users_url;?>"></div>
</div>
<?php if ($employee) { ?>
<div id="additional_clients" class="<?php echo ($dark) ? 'dark' : ''; ?>"></div>
<?php } ?>

<div class="container hide chat-minimized <?php echo ($dark) ? 'dark' : ''; ?>" id="main-chat-window">
	<div class="chat-container main-trans">
		<?php if ($employee) { ?>
		<div class="row" id="main-chat-header">
			<div class="chat-group customers active"><i class="icon-group"></i> Customers</div>
			<div class="chat-group employees"><i class="icon-briefcase"></i> Employees</div>
			<div class="chat-nav toggle"><i class="icon-chevron-down"></i></div>
			<div class="chat-settings dropup">
				<div class="dropdown-toggle" data-toggle="dropdown"><i class="icon-cog"></i></div>
				<ul class="dropdown-menu slidedown">
					<li>
						<a href="#" id="refresh_online_users_list">
							<i class="icon-chevron-right"></i> Refresh Online Users
						</a>
						<a href="#" id="ping_all_customers">
							<i class="icon-chevron-right"></i> Check All Customers
						</a>
						<a href="#" id="enableNotifications">
							<i class="icon-chevron-right"></i> Enable Notifications
						</a>
					</li>
				</ul>
			</div>
		</div>
		<?php } else { ?>
		<div class="row hidden-phone" id="main-chat-header">Live Chat</div>
		<?php } ?>
		<div class="row" id="main-chat-body">
			<?php if ($employee) { ?>
			<div class="row chat-titles" id="customer-chat-clients">
				<div class="no-customers">
					<span class="status-icon"><i class="icon-spin icon-spinner icon-2x"></i></span>
					<p class="status">Checking for Customers</p>
				</div>
			</div>
			<div class="row chat-titles hide" id="employee-chat-clients">
				<div class="no-employees">
					<span class="status-icon"><i class="icon-spin icon-spinner icon-2x"></i></span>
					<p class="status">Checking for Employees</p>
				</div>
			</div>
			<?php } else { ?>
			<ul id="main-chat-messages">
				<li><h4 class="chat-opening-title">Welcome to Live Support Chat!</h4></li>
			</ul>
			<?php } ?>
		</div>
		<?php if (!$employee) { ?>
			<div class="row" id="main-chat-footer">
			<div class="input-append" id="chat-message-input">
				<input id="chat-btn-input" type="text" class="form-control" placeholder="Type your message here">
				<span class="input-group-btn">
					<button class="btn btn-warning" id="chat-send-message-btn">
						Send
					</button>
				</span>
			</div>
		</div>
		<?php } ?>
	</div>
</div>
<div id="main-chat-notice" class="<?php echo ($dark) ? 'dark' : ''; ?> main-trans">
	<?php if ($employee) { ?>
	<span class="main-chat-notice-customers">
		<span class="online-group-icon"><i class="icon-group"></i></span> <span class="label main-chat-notice-online-num"></span>
	</span>
	<?php } else { ?>
		<span class="main-chat-notice-label">Chat Live! <i class="icon-comments"></i></span>
	<?php } ?>
	<span class="main-chat-notice-toggle chat-status main-trans offline"><i class="icon-chevron-up"></i></span>
	<span class="main-chat-notice-messages hide">
		<span class="label label-success main-chat-notice-num-messages blink-me"></span> <i class="icon-comment"></i>
	</span>
</div>