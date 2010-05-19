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
		$("#salesrank_details [name=start], #salesrank_details [name=end]").datepicker({
			dateFormat: "yy-mm-dd",
			changeMonth: true,
			changeYear: true
		});
		// Loction Tree
		var location = $("#salesrank_details [name=location]");
		$("#salesrank_details [name=location_tree]").tree({
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
					<?php foreach ($this->employees as $cur_employee) { ?>
						if (TREE_OBJ.selected.attr("id") == <?php echo $cur_employee->group->guid; ?>) {
							$("#salesrank_details [name=goal_<?php echo $cur_employee->guid; ?>]").show();
						} else {
							$("#salesrank_details [name=goal_<?php echo $cur_employee->guid; ?>]").hide();
						}
					<?php } ?>
				},
				check_move: function() {
					return false;
				}
			}
		});
		
		pines.com_reports_update_goals = function () {
			<?php foreach ($this->employees as $cur_employee) { ?>
			if (location.val() == "<?php echo $cur_employee->group->guid; ?>")
				$("#salesrank_details [name=goals[<?php echo $cur_employee->guid; ?>]]").val($("#salesrank_details [name=goals_updater]").val());
			<?php } ?>
		}
	});
	// ]]>
</script>
<form class="pf-form" method="post" id="salesrank_details" action="<?php echo htmlentities(pines_url('com_reports', 'savesalesranking')); ?>">
	<div class="pf-element">
		<span style="float: left;">Start
		<input class="ui-widget-content" type="text" name="start" value="<?php echo ($this->entity->start_date) ? format_date($this->entity->start_date, 'date_short') : format_date(time(), 'date_short'); ?>" style="text-align: center;" /></span>
		<span style="padding-left: 25px; float: right;">End
		<input class="ui-widget-content" type="text" name="end" value="<?php echo ($this->entity->end_date) ? format_date($this->entity->end_date, 'date_short') : format_date(time(), 'date_short'); ?>" style="text-align: center;" /></span>
	</div>
	<div class="pf-element" name="location_tree"></div>
	<div class="pf-element pf-heading">
		<h1>Sales Goals</h1>
	</div>
	<div class="pf-element" name="employee_goals">
		<div class="pf-element pf-group" style="margin-left: 15px;">
			$<input class="pf-field ui-widget-content" type="text" name="goals_updater" value="<?php echo isset($this->entity->goals[$cur_employee->guid]) ? $this->entity->goals[$cur_employee->guid] : $pines->config->com_reports->default_goal; ?>" size="5" style="color: cornflowerblue;" />
			<input class="ui-corner-all ui-state-default" type="button" value="Update Entire Group" onclick="pines.com_reports_update_goals();" />
		</div>
		<?php foreach ($this->employees as $cur_employee) { ?>
		<div class="pf-element pf-group" name="goal_<?php echo $cur_employee->guid; ?>" style="margin-left: 15px;">
			$<input class="pf-field ui-widget-content" type="text" name="goals[<?php echo $cur_employee->guid; ?>]" value="<?php echo isset($this->entity->goals[$cur_employee->guid]) ? $this->entity->goals[$cur_employee->guid] : $pines->config->com_reports->default_goal; ?>" size="5" />
			<span><?php echo $cur_employee->name; ?></span>
		</div>
		<?php } ?>
	</div>
	<div class="pf-element pf-buttons">
		<?php if (isset($this->entity->guid)) { ?>
		<input type="hidden" name="id" value="<?php echo $this->entity->guid; ?>" />
		<?php } ?>
		<input type="hidden" name="location" />
		<input class="ui-corner-all ui-state-default" type="submit" value="Save" />
		<input class="pf-button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="pines.get('<?php echo htmlentities(pines_url('com_reports', 'salesrankings')); ?>');" value="Cancel" />
	</div>
</form>