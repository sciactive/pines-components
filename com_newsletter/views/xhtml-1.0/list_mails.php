<?php
/**
 * Lists mailings.
 *
 * @package Pines
 * @subpackage com_newsletter
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
?>
<?php foreach($this->mails as $mail) { ?>
<strong><?php echo $mail->name; ?></strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input type="button" onclick="window.location='<?php echo $config->template->url('com_newsletter', 'edit', array('mail_id' => $mail->guid)); ?>';" value="Edit" /> |
<input type="button" onclick="window.location='<?php echo $config->template->url('com_newsletter', 'sendprep', array('mail_id' => $mail->guid)); ?>';" value="Send" /> |
<input type="button" onclick="if(confirm('Are you sure you want to delete \'<?php echo $mail->name; ?>\'?')) {window.location='<?php echo $config->template->url('com_newsletter', 'delete', array('mail_id' => $mail->guid)); ?>';}" value="Delete" />
<br /><br />
<?php } ?>