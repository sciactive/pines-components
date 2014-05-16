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

<?php $employee = ($_SESSION['user']->employee && gatekeeper('com_gae_chat/employeechat')); ?>
<div id="gae-chat-variables" class="hide">
    <div id="send_message_url" class="hide" data-url="<?php echo $pines->config->com_gae_chat->message_url; ?>"></div>
    <div id="get_token_url" class="hide" data-url="<?php echo $pines->config->com_gae_chat->pines_token_url;?>"></div>
    <div id="online_test_url" class="hide" data-url="<?php echo $pines->config->com_gae_chat->ping_test_url;?>"></div>
    <div id="send_online_check_url" class="hide" data-url="<?php echo $pines->config->com_gae_chat->online_check_url;?>"></div>
    <div id="chat_customer_pic" class="hide" data-url="<?php echo $pines->config->com_gae_chat->customer_pic;?>"></div>
    <div id="chat_employee_pic" class="hide" data-url="<?php echo $pines->config->com_gae_chat->employee_pic;?>"></div>
</div>
<?php if ($employee) { ?>
<div id="additional_clients"></div>
<?php } ?>

<div class="container hide" id="main-chat-window">
    <div class="row" id="main-chat-header">
        <span class="chat-status offline"></span>
        <span class="chat-status-text">Offline</span>
        <div class="btn-group pull-right">
            <button type="button" class="btn btn-small" id="min-main-chat-btn">
                    <i class="icon-chevron-down"></i>
            </button>
            <?php if ($employee) { ?>
            <button type="button" class="btn btn-small dropdown-toggle" data-toggle="dropdown">
                <i class="icon-cog"></i>
            </button>
            <ul class="dropdown-menu slidedown">
                <li>
                    <a href="#" id="enableNotifications">
                        <i class="icon-chevron-right"></i> Enable Notifications
                    </a>
                </li>
            </ul>
            <?php } ?>
        </div>
    </div>
    <div class="row" id="main-chat-body">
        <?php if ($employee) { ?>
        <div class="row chat-titles" id="employee-chat-clients">
            <h4 class="main-chat-div-list">Employees</h4>
        </div>
        <div class="row chat-titles" id="customer-chat-clients">
            <h4 class="main-chat-div-list">Customers</h4>
        </div>
        <?php } else { ?>
        <ul id="main-chat-messages"></ul>
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