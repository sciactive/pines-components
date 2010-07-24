<?php
/**
 * Display a form to enter calendar events.
 *
 * Built upon:
 *
 * FullCalendar Created by Adam Shaw
 * http://arshaw.com/fullcalendar/
 *
 * Very Simple Context Menu Plugin by Intekhab A Rizvi
 * http://intekhabrizvi.wordpress.com/
 * 
 * @package Pines
 * @subpackage com_hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
?>
<style type="text/css" >
	/* <![CDATA[ */
	#p_muid_form .form_center {
		text-align: center;
	}
	#p_muid_form .form_input {
		width: 170px;
	}
	/* ]]> */
</style>
<script type='text/javascript'>
// <![CDATA[
	pines(function(){
		$("#p_muid_start").datepicker({
			dateFormat: "yy-mm-dd",
			changeMonth: true,
			changeYear: true,
			showOtherMonths: true,
			selectOtherMonths: true
		});
		$("#p_muid_end").datepicker({
			dateFormat: "yy-mm-dd",
			changeMonth: true,
			changeYear: true,
			showOtherMonths: true,
			selectOtherMonths: true
		});

		// Location Tree
		var location = $("#p_muid_form [name=location]");
		$("#p_muid_form .location_tree")
		.bind("select_node.jstree", function(e, data){
			var selected = data.inst.get_selected().attr("id").replace("p_muid_", "");
			location.val(selected);
			update_employees(selected);
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
				"initially_select" : ["<?php echo (int) $this->location; ?>"]
			}
		});

		var timespan = $("[name=time_start], [name=time_end]", "#p_muid_form");
		$("#p_muid_form [name=all_day]").change(function(){
			if ($(this).is(":checked")) {
				timespan.addClass("ui-state-disabled").attr("disabled", "disabled");
			} else {
				timespan.removeClass("ui-state-disabled").removeAttr("disabled");
			}
		}).change();

		$("#p_muid_start").change(function(){
			var start_date = new Date($(this).val());
			var end_date = new Date($("#p_muid_end").val());
			if (start_date > end_date)
				$("#p_muid_end").val($(this).val());
		}).change();

		// This function reloads the employees when switching between locations.
		var update_employees = function(group_id){
			var employee = $("#p_muid_form [name=employee]");
			employee.empty();
			<?php
			// Load employee departments.
			foreach ($pines->config->com_hrm->employee_departments as $cur_dept) {
				$cur_dept_info = explode(':', $cur_dept);
				$cur_name = $cur_dept_info[0];
				$cur_color = $cur_dept_info[1];
				$cur_select = (!isset($this->entity->employee->group) && $this->entity->employee == $cur_name) ? 'selected=\"selected\"' : '';
			?>
				employee.append("<option value=\"<?php echo htmlentities($cur_name); ?>:<?php echo htmlentities($cur_color); ?>\" <?php echo $cur_select; ?>><?php echo htmlentities($cur_name); ?></option>");
			<?php }
			// Load employees for this location.
			foreach ($this->employees as $cur_employee) {
				if (!isset($cur_employee->group))
					continue;
				$cur_select = (isset($this->entity->employee->group) && $this->entity->employee->is($cur_employee)) ? 'selected=\"selected\"' : ''; ?>
				if (group_id == <?php echo $cur_employee->group->guid; ?>)
					employee.append("<option value=\"<?php echo $cur_employee->guid; ?>\" <?php echo $cur_select; ?>><?php echo htmlentities($cur_employee->name); ?></option>");
			<?php } ?>
		};
	});
// ]]>
</script>
<form class="pf-form" method="post" id="p_muid_form" action="<?php echo htmlentities(pines_url('com_hrm', 'saveevent')); ?>">
	<div class="pf-element location_tree" style="padding-bottom: 1em; width: 90%;"></div>
	<div class="pf-element">
		<select class="ui-widget-content ui-corner-all form_input" name="employee"></select>
	</div>
	<div class="pf-element">
		<input class="ui-widget-content ui-corner-all form_input" type="text" id="p_muid_event_label" name="event_label" value="<?php echo (isset($this->entity->label)) ? htmlentities($this->entity->label) : 'Label'; ?>" onfocus="if(this.value==this.defaultValue)this.value=''" onblur="if(this.value=='')this.value=this.defaultValue" />
	</div>
	<?php
		if ($this->entity->guid) {
			$start_date = format_date($this->entity->start, 'date_sort');
			$start_time = format_date($this->entity->start, 'custom', 'H');
			$end_date = format_date($this->entity->end, 'date_sort');
			$end_time = format_date($this->entity->end, 'custom', 'H');
		}
	?>
	<div class="pf-element">
		<label><input class="pf-field ui-widget-content ui-corner-all" type="checkbox" name="all_day" value="ON" <?php echo ($this->entity->all_day) ? 'checked="checked" ' : ''; ?>/>All Day</label>
	</div>
	<div class="pf-element pf-full-width">
		<span class="pf-note">Start</span><input class="ui-widget-content ui-corner-all form_center" type="text" size="12" id="p_muid_start" name="start" value="<?php echo empty($start_date) ? format_date(time(), 'date_sort') : htmlentities($start_date); ?>" />
		<select class="ui-widget-content ui-corner-all" name="time_start">
			<option value="0" <?php echo ($start_time == '0') ? 'selected="selected"' : ''; ?>>12:00 AM</option>
			<option value="1" <?php echo ($start_time == '1') ? 'selected="selected"' : ''; ?>>1:00 AM</option>
			<option value="2" <?php echo ($start_time == '2') ? 'selected="selected"' : ''; ?>>2:00 AM</option>
			<option value="3" <?php echo ($start_time == '3') ? 'selected="selected"' : ''; ?>>3:00 AM</option>
			<option value="4" <?php echo ($start_time == '4') ? 'selected="selected"' : ''; ?>>4:00 AM</option>
			<option value="5" <?php echo ($start_time == '5') ? 'selected="selected"' : ''; ?>>5:00 AM</option>
			<option value="6" <?php echo ($start_time == '6') ? 'selected="selected"' : ''; ?>>6:00 AM</option>
			<option value="7" <?php echo ($start_time == '7') ? 'selected="selected"' : ''; ?>>7:00 AM</option>
			<option value="8" <?php echo ($start_time == '8') ? 'selected="selected"' : ''; ?>>8:00 AM</option>
			<option value="9" <?php echo ($start_time == '9' || empty($start_time)) ? 'selected="selected"' : ''; ?>>9:00 AM</option>
			<option value="10" <?php echo ($start_time == '10') ? 'selected="selected"' : ''; ?>>10:00 AM</option>
			<option value="11" <?php echo ($start_time == '11') ? 'selected="selected"' : ''; ?>>11:00 AM</option>
			<option value="12" <?php echo ($start_time == '12') ? 'selected="selected"' : ''; ?>>12:00 PM</option>
			<option value="13" <?php echo ($start_time == '13') ? 'selected="selected"' : ''; ?>>1:00 PM</option>
			<option value="14" <?php echo ($start_time == '14') ? 'selected="selected"' : ''; ?>>2:00 PM</option>
			<option value="15" <?php echo ($start_time == '15') ? 'selected="selected"' : ''; ?>>3:00 PM</option>
			<option value="16" <?php echo ($start_time == '16') ? 'selected="selected"' : ''; ?>>4:00 PM</option>
			<option value="17" <?php echo ($start_time == '17') ? 'selected="selected"' : ''; ?>>5:00 PM</option>
			<option value="18" <?php echo ($start_time == '18') ? 'selected="selected"' : ''; ?>>6:00 PM</option>
			<option value="19" <?php echo ($start_time == '19') ? 'selected="selected"' : ''; ?>>7:00 PM</option>
			<option value="20" <?php echo ($start_time == '20') ? 'selected="selected"' : ''; ?>>8:00 PM</option>
			<option value="21" <?php echo ($start_time == '21') ? 'selected="selected"' : ''; ?>>9:00 PM</option>
			<option value="22" <?php echo ($start_time == '22') ? 'selected="selected"' : ''; ?>>10:00 PM</option>
			<option value="23" <?php echo ($start_time == '23') ? 'selected="selected"' : ''; ?>>11:00 PM</option>
		</select>
	</div>
	<div class="pf-element">
		<span class="pf-note">End</span><input class="ui-widget-content ui-corner-all form_center" type="text" size="12" id="p_muid_end" name="end" value="<?php echo empty($end_date) ? format_date(time(), 'date_sort') : htmlentities($end_date); ?>" />
		<select class="ui-widget-content ui-corner-all" name="time_end">
			<option value="0" <?php echo ($end_time == '0') ? 'selected="selected"' : ''; ?>>12:00 AM</option>
			<option value="1" <?php echo ($end_time == '1') ? 'selected="selected"' : ''; ?>>1:00 AM</option>
			<option value="2" <?php echo ($end_time == '2') ? 'selected="selected"' : ''; ?>>2:00 AM</option>
			<option value="3" <?php echo ($end_time == '3') ? 'selected="selected"' : ''; ?>>3:00 AM</option>
			<option value="4" <?php echo ($end_time == '4') ? 'selected="selected"' : ''; ?>>4:00 AM</option>
			<option value="5" <?php echo ($end_time == '5') ? 'selected="selected"' : ''; ?>>5:00 AM</option>
			<option value="6" <?php echo ($end_time == '6') ? 'selected="selected"' : ''; ?>>6:00 AM</option>
			<option value="7" <?php echo ($end_time == '7') ? 'selected="selected"' : ''; ?>>7:00 AM</option>
			<option value="8" <?php echo ($end_time == '8') ? 'selected="selected"' : ''; ?>>8:00 AM</option>
			<option value="9" <?php echo ($end_time == '9') ? 'selected="selected"' : ''; ?>>9:00 AM</option>
			<option value="10" <?php echo ($end_time == '10') ? 'selected="selected"' : ''; ?>>10:00 AM</option>
			<option value="11" <?php echo ($end_time == '11') ? 'selected="selected"' : ''; ?>>11:00 AM</option>
			<option value="12" <?php echo ($end_time == '12') ? 'selected="selected"' : ''; ?>>12:00 PM</option>
			<option value="13" <?php echo ($end_time == '13') ? 'selected="selected"' : ''; ?>>1:00 PM</option>
			<option value="14" <?php echo ($end_time == '14') ? 'selected="selected"' : ''; ?>>2:00 PM</option>
			<option value="15" <?php echo ($end_time == '15') ? 'selected="selected"' : ''; ?>>3:00 PM</option>
			<option value="16" <?php echo ($end_time == '16') ? 'selected="selected"' : ''; ?>>4:00 PM</option>
			<option value="17" <?php echo ($end_time == '17' || empty($end_time)) ? 'selected="selected"' : ''; ?>>5:00 PM</option>
			<option value="18" <?php echo ($end_time == '18') ? 'selected="selected"' : ''; ?>>6:00 PM</option>
			<option value="19" <?php echo ($end_time == '19') ? 'selected="selected"' : ''; ?>>7:00 PM</option>
			<option value="20" <?php echo ($end_time == '20') ? 'selected="selected"' : ''; ?>>8:00 PM</option>
			<option value="21" <?php echo ($end_time == '21') ? 'selected="selected"' : ''; ?>>9:00 PM</option>
			<option value="22" <?php echo ($end_time == '22') ? 'selected="selected"' : ''; ?>>10:00 PM</option>
			<option value="23" <?php echo ($end_time == '23') ? 'selected="selected"' : ''; ?>>11:00 PM</option>
		</select>
	</div>
	<input type="hidden" name="location" value="<?php echo htmlentities($this->location); ?>" />
	<?php if (isset($this->entity->guid)) { ?>
	<input type="hidden" name="id" value="<?php echo $this->entity->guid; ?>" />
	<?php } ?>
</form>