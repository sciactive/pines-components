<?php
/**
 * A view to load GAE Chat for the customer
 *
 * @package Components\gae_chat
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Mohammed Ahmed <mohammedsadikahmed@gmail.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');
?>

<link rel="stylesheet" href="<?php echo $pines->config->full_location; ?>components/com_gae_chat/includes/customer_chat.css"></link> 

<script type="text/javascript" src="https://chat-dot-webapp108.appspot.com/_ah/channel/jsapi"></script>
<script type="text/javascript" src="<?php echo $pines->config->full_location; ?>components/com_timeago/includes/jquery.timeago.js"></script>
<script type="text/javascript" src="<?php echo $pines->config->full_location; ?>components/com_gae_chat/includes/customer_chat.js"></script>