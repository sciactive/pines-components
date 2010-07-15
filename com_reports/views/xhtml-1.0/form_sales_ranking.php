<?php
/**
 * Display a form to edit the details of a sales ranking.
 *
 * @package Pines
 * @subpackage com_reports
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

$this->title = isset($this->entity->guid) ? 'Editing Sales Ranking ['.$this->entity->guid.']' : 'New Sales Ranking';
$pines->com_jstree->load();
?>
<script type='text/javascript'>
	// <![CDATA[
	pines(function(){
		$("#p_muid_form [name=start], #p_muid_form [name=end]").datepicker({
			dateFormat: "yy-mm-dd",
			changeMonth: true,
			changeYear: true,
			showOtherMonths: true,
			selectOtherMonths: true
		});
		// Location Tree
		var top_location = $("#p_muid_form [name=top_location]");
		$("#p_muid_form .location_tree")
		.bind("select_node.jstree", function(e, data){
			top_location.val(data.inst.get_selected().attr("id").replace("p_muid_", ""));
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
					"url" : "<?php echo addslashes(pines_url('com_jstree', 'groupjson')); ?>"
				}
			},
			"ui" : {
				"select_limit" : 1,
				"initially_select" : ["p_muid_<?php echo (int) $this->entity->top_location->guid; ?>"]
			}
		});
		
		// Location Tree
		var location = $("#p_muid_form [name=location]");
		$("#p_muid_form .goals_tree")
		.bind("select_node.jstree", function(e, data){
			var selected_id = data.inst.get_selected().attr("id").replace("p_muid_", "");
			location.val(selected_id);
			<?php foreach ($this->employees as $cur_employee) { ?>
			if (selected_id == "<?php echo $cur_employee->group->guid; ?>")
				$("#p_muid_form .goal_<?php echo $cur_employee->guid; ?>").show();
			else
				$("#p_muid_form .goal_<?php echo $cur_employee->guid; ?>").hide();
			<?php } ?>
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
					"url" : "<?php echo addslashes(pines_url('com_jstree', 'groupjson')); ?>"
				}
			},
			"ui" : {
				"select_limit" : 1,
				"initially_select" : ["p_muid_<?php echo (int) $this->entity->top_location->guid; ?>"]
			}
		});
		
		pines.com_reports_update_goals = function () {
			<?php foreach ($this->employees as $cur_employee) { ?>
			if (location.val() == "<?php echo $cur_employee->group->guid; ?>")
				$("#p_muid_form [name=goals[<?php echo $cur_employee->guid; ?>]]").val($("#p_muid_form [name=goals_updater]").val());
			<?php } ?>
		}
	});
	// ]]>
</script>
<form class="pf-form" method="post" id="p_muid_form" action="<?php echo htmlentities(pines_url('com_reports', 'savesalesranking')); ?>">
	<div class="pf-element">
		<label><span class="pf-label">Ranking Name</span>
			<input class="pf-field ui-widget-content" type="text" name="ranking_name" value="<?php echo htmlentities($this->entity->name); ?>" /></label>
	</div>
	
	<div class="pf-element pf-heading">
		<h1>Timespan and Highest Company Division</h1>
	</div>
	<div class="pf-element">
		<div style="float: left;">
			<span class="pf-label">Start Date
			<input class="ui-widget-content" type="text" name="start" value="<?php echo ($this->entity->start_date) ? format_date($this->entity->start_date, 'date_sort') : format_date(time(), 'date_sort'); ?>" style="text-align: center;" /></span><br />
			<span class="pf-label">End Date
			<input class="ui-widget-content" type="text" name="end" value="<?php echo ($this->entity->end_date) ? format_date($this->entity->end_date, 'date_sort') : format_date(time(), 'date_sort'); ?>" style="text-align: center;" /></span>
		</div>
		<div class="location_tree" style="padding: 15px 0 0 50px; float: right;"></div>
	</div>
	<div class="pf-element pf-heading">
		<h1>Sales Goals</h1>
	</div>
	<div class="pf-element goals_tree"></div>
	<div class="pf-element">
		<div class="pf-element pf-group" style="margin-left: 15px;">
			$<input class="pf-field ui-widget-content" type="text" name="goals_updater" value="<?php echo isset($this->entity->goals[$cur_employee->guid]) ? htmlentities($this->entity->goals[$cur_employee->guid]) : htmlentities($pines->config->com_reports->default_goal); ?>" size="5" style="color: cornflowerblue;" />
			<input class="ui-corner-all ui-state-default" type="button" value="Update Entire Group" onclick="pines.com_reports_update_goals();" />
		</div>
		<?php foreach ($this->employees as $cur_employee) { ?>
		<div class="pf-element pf-group goal_<?php echo $cur_employee->guid; ?>" style="margin-left: 15px;">
			$<input class="pf-field ui-widget-content" type="text" name="goals[<?php echo $cur_employee->guid; ?>]" value="<?php echo isset($this->entity->goals[$cur_employee->guid]) ? htmlentities($this->entity->goals[$cur_employee->guid]) : htmlentities($pines->config->com_reports->default_goal); ?>" size="5" />
			<span><?php echo htmlentities($cur_employee->name); ?></span>
		</div>
		<?php } ?>
	</div>
	<div class="pf-element pf-buttons">
		<?php if (isset($this->entity->guid)) { ?>
		<input type="hidden" name="id" value="<?php echo $this->entity->guid; ?>" />
		<?php } ?>
		<input type="hidden" name="top_location" />
		<input type="hidden" name="location" />
		<input class="ui-corner-all ui-state-default" type="submit" value="Save" />
		<input class="pf-button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="pines.get('<?php echo htmlentities(pines_url('com_reports', 'salesrankings')); ?>');" value="Cancel" />
	</div>
</form>