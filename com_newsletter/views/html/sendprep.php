<?php
/**
 * Provides a form with options for sending a newsletter.
 *
 * @package Components
 * @subpackage newsletter
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Sending '.htmlspecialchars($this->mail->name);
$pines->com_jstree->load();
?>
<script type='text/javascript'>
	pines(function(){
		// Location Tree
		var location = $("#p_muid_form [name=location]");
		$("#p_muid_form .location_tree")
		.bind("select_node.jstree", function(e, data){
			location.val(data.inst.get_selected().attr("id").replace("p_muid_", ""));
		})
		.bind("before.jstree", function (e, data){
			if (data.func == "parse_json" && "args" in data && 0 in data.args && "attr" in data.args[0] && "id" in data.args[0].attr)
				data.args[0].attr.id = "p_muid_"+data.args[0].attr.id;
		})
		.bind("loaded.jstree", function(e, data){
			var path = data.inst.get_path("#"+data.inst.get_settings().ui.initially_select, true);
			if (!path.length) return;
			data.inst.open_node("#"+path.join(", #"), false, true);
		})
		.jstree({
			"plugins" : [ "themes", "json_data", "ui" ],
			"json_data" : {
				"ajax" : {
					"dataType" : "json",
					"url" : <?php echo json_encode(pines_url('com_jstree', 'groupjson')); ?>
				}
			},
			"ui" : {
				"select_limit" : 1,
				"initially_select" : ["<?php echo (int) $_SESSION['user']->group->guid; ?>"]
			}
		});
	});
</script>
<form class="pf-form" id="p_muid_form" method="post" action="<?php echo htmlspecialchars(pines_url('com_newsletter', 'send')); ?>">
	<div class="pf-element">
		<label><span class="pf-label">From Address</span>
		<input class="pf-field" type="text" name="from" size="24" value="<?php echo htmlspecialchars($pines->config->com_newsletter->default_from); ?>" /></label>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Reply to Address</span>
		<input class="pf-field" type="text" name="replyto" size="24" value="<?php echo htmlspecialchars($pines->config->com_newsletter->default_reply_to); ?>" /></label>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Subject</span>
		<input class="pf-field" type="text" name="subject" size="24" value="<?php echo htmlspecialchars($this->mail->subject); ?>" /></label>
	</div>
	<div class="pf-element">
		<span class="pf-label">Select Groups</span>
		<span class="pf-note">Click group name to select children as well.</span>
	</div>
	<div class="pf-element location_tree"></div>
	<div class="pf-element pf-heading">
		<h3>Options</h3>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Include a link to the mail's web address.</span>
		<span class="pf-note">For online viewing.</span>
		<input class="pf-field" type="checkbox" name="include_permalink" checked /></label>
	</div>
	<div class="pf-element pf-buttons">
		<input type="hidden" name="mail_id" value="<?php echo htmlspecialchars($_REQUEST['mail_id']); ?>" />
		<input type="hidden" name="location" />
		<input class="pf-button btn btn-primary" type="submit" value="Submit" />
		<input class="pf-button btn" type="button" onclick="pines.get(<?php echo htmlspecialchars(json_encode(pines_url('com_newsletter', 'list'))); ?>);" value="Cancel" />
	</div>
</form>