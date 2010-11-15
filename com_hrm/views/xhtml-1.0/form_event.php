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

		var timespan = $("[name=time_start_hour], [name=time_start_minute], [name=time_start_ampm], [name=time_end_hour], [name=time_end_minute], [name=time_end_ampm],", "#p_muid_form");
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
				employee.append("<option value=\"<?php echo htmlspecialchars($cur_name); ?>:<?php echo htmlspecialchars($cur_color); ?>\" <?php echo $cur_select; ?>><?php echo htmlspecialchars($cur_name); ?></option>");
			<?php }
			// Load employees for this location.
			foreach ($this->employees as $cur_employee) {
				if (!isset($cur_employee->group))
					continue;
				$cur_select = (isset($this->entity->employee->group) && $this->entity->employee->is($cur_employee)) ? 'selected=\"selected\"' : ''; ?>
				if (group_id == <?php echo $cur_employee->group->guid; ?>)
					employee.append("<option value=\"<?php echo $cur_employee->guid; ?>\" <?php echo $cur_select; ?>><?php echo htmlspecialchars($cur_employee->name); ?></option>");
			<?php } ?>
		};
	});
// ]]>
</script>
<form class="pf-form" method="post" id="p_muid_form" action="<?php echo htmlspecialchars(pines_url('com_hrm', 'saveevent')); ?>">
	<div class="pf-element location_tree" style="padding-bottom: 1em; width: 90%;"></div>
	<div class="pf-element">
		<select class="ui-widget-content ui-corner-all form_input" name="employee"></select>
	</div>
	<div class="pf-element">
		<input class="ui-widget-content ui-corner-all form_input" type="text" id="p_muid_event_label" name="event_label" value="<?php echo (isset($this->entity->label)) ? htmlspecialchars($this->entity->label) : 'Label'; ?>" onfocus="if(this.value==this.defaultValue)this.value=''" onblur="if(this.value=='')this.value=this.defaultValue" />
	</div>
	<?php
		if ($this->entity->guid) {
			$start_date = format_date($this->entity->start, 'date_sort');
			$start_hour = format_date($this->entity->start, 'custom', 'H');
			$start_minute = format_date($this->entity->start, 'custom', 'i');
			$end_date = format_date($this->entity->end, 'date_sort');
			$end_hour = format_date($this->entity->end, 'custom', 'H');
			$end_minute = format_date($this->entity->end, 'custom', 'i');
		}
	?>
	<div class="pf-element">
		<label><input class="pf-field" type="checkbox" name="all_day" value="ON" <?php echo ($this->entity->all_day) ? 'checked="checked" ' : ''; ?>/>All Day</label>
	</div>
	<div class="pf-element pf-full-width">
		<span class="pf-note">Start</span>
		<input class="ui-widget-content ui-corner-all form_center" type="text" size="24" id="p_muid_start" name="start" value="<?php echo empty($start_date) ? format_date(time(), 'date_sort') : htmlspecialchars($start_date); ?>" />
	</div>
	<div class="pf-element pf-full-width">
		<select class="ui-widget-content ui-corner-all" name="time_start_hour">
			<option value="1" <?php echo ($start_hour == '1' || $start_hour == '13') ? 'selected="selected"' : ''; ?>>1</option>
			<option value="2" <?php echo ($start_hour == '2' || $start_hour == '14') ? 'selected="selected"' : ''; ?>>2</option>
			<option value="3" <?php echo ($start_hour == '3' || $start_hour == '15') ? 'selected="selected"' : ''; ?>>3</option>
			<option value="4" <?php echo ($start_hour == '4' || $start_hour == '16') ? 'selected="selected"' : ''; ?>>4</option>
			<option value="5" <?php echo ($start_hour == '5' || $start_hour == '17') ? 'selected="selected"' : ''; ?>>5</option>
			<option value="6" <?php echo ($start_hour == '6' || $start_hour == '18') ? 'selected="selected"' : ''; ?>>6</option>
			<option value="7" <?php echo ($start_hour == '7' || $start_hour == '19') ? 'selected="selected"' : ''; ?>>7</option>
			<option value="8" <?php echo ($start_hour == '8' || $start_hour == '20') ? 'selected="selected"' : ''; ?>>8</option>
			<option value="9" <?php echo ($start_hour == '9' || $start_hour == '21' || empty($start_hour)) ? 'selected="selected"' : ''; ?>>9</option>
			<option value="10" <?php echo ($start_hour == '10' || $start_hour == '22') ? 'selected="selected"' : ''; ?>>10</option>
			<option value="11" <?php echo ($start_hour == '11' || $start_hour == '23') ? 'selected="selected"' : ''; ?>>11</option>
			<option value="0" <?php echo ($start_hour == '0' || $start_hour == '12') ? 'selected="selected"' : ''; ?>>12</option>
		</select> :
		<select class="ui-widget-content ui-corner-all" name="time_start_minute">
			<option value="0" <?php echo ($start_minute == '0'  || empty($start_minute)) ? 'selected="selected"' : ''; ?>>00</option>
			<option value="15" <?php echo ($start_minute == '15') ? 'selected="selected"' : ''; ?>>15</option>
			<option value="30" <?php echo ($start_minute == '30') ? 'selected="selected"' : ''; ?>>30</option>
			<option value="45" <?php echo ($start_minute == '45') ? 'selected="selected"' : ''; ?>>45</option>
		</select>
		<select class="ui-widget-content ui-corner-all" name="time_start_ampm">
			<option value="am" selected="selected">AM</option>
			<option value="pm" <?php echo ($start_hour >= 12) ? 'selected="selected"' : ''; ?>>PM</option>
		</select>
	</div>
	<div class="pf-element">
		<span class="pf-note">End</span>
		<input class="ui-widget-content ui-corner-all form_center" type="text" size="24" id="p_muid_end" name="end" value="<?php echo empty($end_date) ? format_date(time(), 'date_sort') : htmlspecialchars($end_date); ?>" />
	</div>
	<div class="pf-element pf-full-width">
		<select class="ui-widget-content ui-corner-all" name="time_end_hour">
			<option value="1" <?php echo ($end_hour == '1' || $end_hour == '13') ? 'selected="selected"' : ''; ?>>1</option>
			<option value="2" <?php echo ($end_hour == '2' || $end_hour == '14') ? 'selected="selected"' : ''; ?>>2</option>
			<option value="3" <?php echo ($end_hour == '3' || $end_hour == '15') ? 'selected="selected"' : ''; ?>>3</option>
			<option value="4" <?php echo ($end_hour == '4' || $end_hour == '16') ? 'selected="selected"' : ''; ?>>4</option>
			<option value="5" <?php echo ($end_hour == '5' || $end_hour == '17' || empty($end_hour)) ? 'selected="selected"' : ''; ?>>5</option>
			<option value="6" <?php echo ($end_hour == '6' || $end_hour == '18') ? 'selected="selected"' : ''; ?>>6</option>
			<option value="7" <?php echo ($end_hour == '7' || $end_hour == '19') ? 'selected="selected"' : ''; ?>>7</option>
			<option value="8" <?php echo ($end_hour == '8' || $end_hour == '20') ? 'selected="selected"' : ''; ?>>8</option>
			<option value="9" <?php echo ($end_hour == '9' || $end_hour == '21') ? 'selected="selected"' : ''; ?>>9</option>
			<option value="10" <?php echo ($end_hour == '10' || $end_hour == '22') ? 'selected="selected"' : ''; ?>>10</option>
			<option value="11" <?php echo ($end_hour == '11' || $end_hour == '23') ? 'selected="selected"' : ''; ?>>11</option>
			<option value="0" <?php echo ($end_hour == '0' || $end_hour == '12') ? 'selected="selected"' : ''; ?>>12</option>
		</select> :
		<select class="ui-widget-content ui-corner-all" name="time_end_minute">
			<option value="0" <?php echo ($end_minute == '0' || empty($end_minute)) ? 'selected="selected"' : ''; ?>>00</option>
			<option value="15" <?php echo ($end_minute == '15') ? 'selected="selected"' : ''; ?>>15</option>
			<option value="30" <?php echo ($end_minute == '30') ? 'selected="selected"' : ''; ?>>30</option>
			<option value="45" <?php echo ($end_minute == '45') ? 'selected="selected"' : ''; ?>>45</option>
		</select>
		<select class="ui-widget-content ui-corner-all" name="time_end_ampm">
			<option value="am" selected="selected">AM</option>
			<option value="pm" <?php echo ($end_hour >= 12 || empty($end_hour)) ? 'selected="selected"' : ''; ?>>PM</option>
		</select>
	</div>
	<input type="hidden" name="location" value="<?php echo htmlspecialchars($this->location); ?>" />
	<?php if (isset($this->entity->guid)) { ?>
	<input type="hidden" name="id" value="<?php echo $this->entity->guid; ?>" />
	<?php } ?>
</form>