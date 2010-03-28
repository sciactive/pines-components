<?php
/**
 * Includes for the calendar.
 *
 * @package Pines
 * @subpackage com_hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zhuber@gmail.com>
 * @copyright Zak Huber
 * @link http://sciactive.com/
 *
 * Built upon:
 *
 * FullCalendar Created by Adam Shaw
 * http://arshaw.com/fullcalendar/
 *
 * Very Simple Context Menu Plugin by Intekhab A Rizvi
 * http://intekhabrizvi.wordpress.com/
 */
defined('P_RUN') or die('Direct access prohibited');
?>
<?php // Calendar ?>
<link rel="stylesheet" type="text/css" href="<?php echo $pines->config->rela_location; ?>components/com_hrm/includes/fullcalendar.css" />
<script type="text/javascript" src="<?php echo $pines->config->rela_location; ?>components/com_hrm/includes/fullcalendar.min.js"></script>
<?php if (gatekeeper('com_hrm/editcalendar')) { ?>
<link rel="stylesheet" type="text/css" href="<?php echo $pines->config->rela_location; ?>components/com_hrm/includes/context/css/vscontext.css" />
<script type="text/javascript" src="<?php echo $pines->config->rela_location; ?>components/com_hrm/includes/context/vscontext.jquery.js"></script>
<script type="text/javascript" src="<?php echo $pines->config->rela_location; ?>components/com_hrm/includes/context/menu_action.js"></script>
<?php } ?>