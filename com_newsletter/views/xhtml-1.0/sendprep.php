<?php
/**
 * Provides a form with options for sending a newsletter.
 *
 * @package Pines
 * @subpackage com_newsletter
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = "Sending {$this->mail->name}";
$pines->com_jstree->load();
?>
<script type='text/javascript'>
	// <![CDATA[
	pines(function(){
		// Location Tree
		var location = $("#mail_details [name=location]");
		$("#mail_details .location_tree").tree({
			rules : {
				multiple : false
			},
			data : {
				type : "json",
				opts : {
					method : "get",
					url : "<?php echo pines_url('com_jstree', 'groupjson'); ?>"
				}
			},
			selected : ["<?php echo $_SESSION['user']->group->guid; ?>"],
			callback : {
				onchange : function(NODE, TREE_OBJ) {
					location.val(TREE_OBJ.selected.attr("id"));
				},
				check_move: function() {
					return false;
				}
			}
		});
	});
	// ]]>
</script>
<form class="pf-form" id="mail_details" method="post" action="<?php echo htmlentities(pines_url('com_newsletter', 'send')); ?>">
	<div class="pf-element">
		<label><span class="pf-label">From Address</span>
		<input class="pf-field ui-widget-content" type="text" name="from" size="24" value="<?php echo htmlentities($pines->config->com_newsletter->default_from); ?>" /></label>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Reply to Address</span>
		<input class="pf-field ui-widget-content" type="text" name="replyto" size="24" value="<?php echo htmlentities($pines->config->com_newsletter->default_reply_to); ?>" /></label>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Subject</span>
		<input class="pf-field ui-widget-content" type="text" name="subject" size="24" value="<?php echo htmlentities($this->mail->subject); ?>" /></label>
	</div>
	<div class="pf-element">
		<span class="pf-label">Select Groups</span>
		<span class="pf-note">Click group name to select children as well.</span>
	</div>
	<div class="pf-element location_tree"></div>
	<div class="pf-element pf-heading">
		<h1>Options</h1>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Include a link to the mail's web address.</span>
		<span class="pf-note">For online viewing.</span>
		<input class="pf-field ui-widget-content" type="checkbox" name="include_permalink" checked /></label>
	</div>
	<div class="pf-element pf-buttons">
		<input type="hidden" name="mail_id" value="<?php echo $_REQUEST['mail_id']; ?>" />
		<input type="hidden" name="location" />
		<input class="pf-button ui-state-default ui-priority-primary ui-corner-all" type="submit" value="Submit" />
		<input class="pf-button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="pines.get('<?php echo htmlentities(pines_url('com_newsletter', 'list')); ?>');" value="Cancel" />
	</div>
</form>