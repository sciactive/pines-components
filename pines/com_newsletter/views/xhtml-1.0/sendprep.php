<?php
/**
 * Provides a form with options for sending a newsletter.
 *
 * @package Pines
 * @subpackage com_newsletter
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = "Sending {$this->mail->name}";
?>
<script type='text/javascript'>
	// <![CDATA[
	$(function(){
		// Category Tree
		var location = $("#location");
		$("#location_tree").tree({
			rules : {
				multiple : false
			},
			data : {
				type : "json",
				opts : {
					method : "get",
					url : "<?php echo pines_url('com_reports', 'groupjson'); ?>"
				}
			},
			selected : ["<?php echo $_SESSION['user']->group->guid; ?>"],
			callback : {
				onchange : function(NODE, TREE_OBJ) {
					location.val(TREE_OBJ.selected.attr("id"));
				},
				check_move: function(NODE, REF_NODE, TYPE, TREE_OBJ) {
					return false;
				}
			}
		});
	});
	// ]]>
</script>
<form class="pform" method="post" action="<?php echo pines_url('com_newsletter', 'send'); ?>">
	<div class="element">
		<label><span class="label">From Address</span>
		<input class="field ui-widget-content" type="text" name="from" size="24" value="<?php echo htmlentities($pines->config->com_newsletter->default_from); ?>" /></label>
	</div>
	<div class="element">
		<label><span class="label">Reply to Address</span>
		<input class="field ui-widget-content" type="text" name="replyto" size="24" value="<?php echo htmlentities($pines->config->com_newsletter->default_reply_to); ?>" /></label>
	</div>
	<div class="element">
		<label><span class="label">Subject</span>
		<input class="field ui-widget-content" type="text" name="subject" size="24" value="<?php echo htmlentities($this->mail->subject); ?>" /></label>
	</div>
	<div class="element">
		<span class="label">Select Groups</span>
		<span class="note">Click group name to select children as well.</span>
	</div>
	<div class="element" id="location_tree"></div>
	<div class="element heading">
		<h1>Options</h1>
	</div>
	<div class="element">
		<label><span class="label">Include a link to the mail's web address.</span>
		<span class="note">For online viewing.</span>
		<input class="field ui-widget-content" type="checkbox" name="include_permalink" checked /></label>
	</div>
	<div class="element buttons">
		<input type="hidden" name="mail_id" value="<?php echo $_REQUEST['mail_id']; ?>" />
		<input type="hidden" name="location" id="location" />
		<input class="button ui-state-default ui-priority-primary ui-corner-all" type="submit" value="Submit" />
		<input class="button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="pines.get('<?php echo pines_url('com_newsletter', 'list'); ?>');" value="Cancel" />
	</div>
</form>