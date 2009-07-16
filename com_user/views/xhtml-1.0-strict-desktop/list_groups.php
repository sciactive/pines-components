<?php
/**
 * Lists groups and provides functions to manipulate them.
 *
 * @package Dandelion
 * @subpackage com_user
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('D_RUN') or die('Direct access prohibited');
?>
<?php foreach($this->groups as $group) { ?>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong><?php echo $group->groupname; ?></strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input type="button" onclick="window.location='<?php echo $config->template->url('com_user', 'editgroup', array('group_id' => urlencode($group->guid))); ?>';" value="Edit" />
<input type="button" onclick="if(confirm('Are you sure you want to delete \'<?php echo $group->groupname; ?>\'?')) {window.location='<?php echo $config->template->url('com_user', 'deletegroup', array('group_id' => urlencode($group->guid))); ?>';}" value="Delete" />
<br /><br />
<?php } ?>