<?php
/**
 * Lists users and provides functions to manipulate them.
 *
 * @package Pines
 * @subpackage com_user
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
?>
<?php foreach($this->users as $user) { ?>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong><?php echo $user->username; ?></strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input type="button" onclick="window.location='<?php echo $config->template->url('com_user', 'edituser', array('user_id' => urlencode($user->guid))); ?>';" value="Edit" />
<input type="button" onclick="if(confirm('Are you sure you want to delete \'<?php echo $user->username; ?>\'?')) {window.location='<?php echo $config->template->url('com_user', 'deleteuser', array('user_id' => urlencode($user->guid))); ?>';}" value="Delete" />
<br /><br />
<?php } ?>