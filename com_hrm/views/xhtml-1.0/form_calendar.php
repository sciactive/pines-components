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

$this->title = (!isset($this->event->guid)) ? 'New Event' : $this->event->title;
$pines->com_jstree->load();
?>
<style type="text/css" >
	/* <![CDATA[ */
	.form_text {
		width: 155px;
		text-align: center;
	}
	.form_input {
		width: 170px;
		text-align: center;
	}
	.form_select {
		width: 170px;
	}
	/* ]]> */
</style>
<script type='text/javascript'>
// <![CDATA[
	pines(function(){
		$("#event_date").datepicker({
			dateFormat: "m/d/yy",
			changeMonth: true,
			changeYear: true
		});
		$("#event_enddate").datepicker({
			dateFormat: "m/d/yy",
			changeMonth: true,
			changeYear: true
		});

		// Location Tree
		var location = $("#calendar_details [name=location]");
		$("#calendar_details [name=location_tree]").tree({
			rules : {
				multiple : false
			},
			data : {
				type : "json",
				opts : {
					method : "get",
					url : "<?php echo pines_url('com_hrm', 'groupjson'); ?>"
				}
			},
			selected : ["<?php echo $this->location; ?>"],
			callback : {
				onchange : function(NODE, TREE_OBJ) {
					location.val(TREE_OBJ.selected.attr("id"));
				},
				check_move: function(NODE, REF_NODE, TYPE, TREE_OBJ) {
					return false;
				}
			}
		});

		var employee = $("#calendar_details [name=employee]");
		var employees = $("#calendar_details [name=employees]");
		var location_tree = $("#calendar_details [name=location_tree]");
		$("#calendar_details [name=event_type]").change(function(){
			if ($(this).is(":checked") && $(this).val() == "employee") {
				employee.empty();
				<?php
				foreach ($this->employees as $cur_employee) {
				$cur_select = ($this->event->employee == $cur_employee->name) ? 'selected=\"selected\"' : ''; ?>
				employee.append("<option value='<?php echo $cur_employee->name.':'.$cur_employee->color; ?>' <?php echo $cur_select; ?>><?php echo $cur_employee->name; ?></option>");
				<?php } ?>
				location_tree.hide();
			} else if ($(this).is(":checked") && $(this).val() == "location") {
				employee.empty();
				<?php
				$employee_depts = explode(', ', $pines->config->com_hrm->employee_departments);
				foreach ($employee_depts as $cur_dept) {
					$cur_dept_info = explode(':', $cur_dept);
					$cur_name = $cur_dept_info[0];
					$cur_color = $cur_dept_info[1];
					$cur_select = ($this->event->employee == $cur_name) ? 'selected=\"selected\"' : ''; ?>
				employee.append("<option value='<?php echo $cur_name.':'.$cur_color; ?>' <?php echo $cur_select; ?>><?php echo $cur_name; ?></option>");
				<?php } ?>
				location_tree.show();
			}
			$("#calendar_details [name=employee]").change();
		}).change();

		var timespan = $("#calendar_details [name=timespan]");
		$("#calendar_details [name=all_day]").change(function(){
			var all_day = $(this);
			if (all_day.is(":checked") && all_day.val() == "timeSpan") {
				timespan.show();
			} else if (all_day.is(":checked") && all_day.val() == "allDay") {
				timespan.hide();
			}
		}).change();

		$("#event_date").change(function(){
			$("#event_enddate").val($(this).val());
		}).change();
	});
// ]]>
</script>
<form class="pf-form" method="post" id="calendar_details" action="<?php echo htmlentities(pines_url('com_hrm', 'saveevent')); ?>">
	<div class="pf-element" style="padding-bottom: 5px;">
		<label><input class="pf-field ui-widget-content" type="radio" name="event_type" value="location" checked="checked" />Location</label>
		<label><input class="pf-field ui-widget-content" type="radio" name="event_type" value="employee" <?php echo ($this->event->type == 'employee') ? 'checked="checked"' : ''; ?>/>Employee</label>
	</div>
	<div class="pf-element" name="location_tree" style="padding-bottom: 5px;"></div>
	<div class="pf-element" name="employees" style="padding-bottom: 25px;">
		<select class="ui-widget-content form_select" name="employee"></select>
	</div>
	<div class="pf-element" style="padding-bottom: 0px;">
		<input class="ui-widget-content form_text" type="text" id="event_label" name="event_label" value="<?php echo (isset($this->event->label)) ? $this->event->label : 'Label'; ?>" onfocus="if(this.value==this.defaultValue)this.value=''" onblur="if(this.value=='')this.value=this.defaultValue" />
	</div>
	<?php
		if ($this->event->guid) {
			$start_date = format_date($this->event->start, 'custom', 'n/j/Y');
			$start_time = format_date($this->event->start, 'custom', 'H');
			$end_date = format_date($this->event->end, 'custom', 'n/j/Y');
			$end_time = format_date($this->event->end, 'custom', 'H');
		}
	?>
	<div class="pf-element" style="padding-bottom: 0px;">
		<span class="pf-note">Start</span><input class="ui-widget-content form_text" type="text" id="event_date" name="event_date" value="<?php echo empty($start_date) ? date('n/j/Y') : $start_date; ?>" />
	</div>
	<div class="pf-element" style="padding-bottom: 25px;">
		<span class="pf-note">End</span><input class="ui-widget-content form_text" type="text" id="event_enddate" name="event_enddate" value="<?php echo $end_date; ?>" />
	</div>
	<div class="pf-element">
		<label><input class="pf-field ui-widget-content" type="radio" name="all_day" value="timeSpan" checked="checked" />Timespan</label>
		<label><input class="pf-field ui-widget-content" type="radio" name="all_day" value="allDay" />All Day</label>
	</div>
	<div name="timespan" style="text-align: center;">
		<div class="pf-element">
			<label><select class="ui-widget-content" name="event_start">
					<option value="24" <?php echo ($start_time == '24') ? 'selected="selected"' : ''; ?>>12:00 AM</option>
					<option value="1" <?php echo ($start_time == '1') ? 'selected="selected"' : ''; ?>>1:00 AM</option>
					<option value="2" <?php echo ($start_time == '2') ? 'selected="selected"' : ''; ?>>2:00 AM</option>
					<option value="3" <?php echo ($start_time == '3') ? 'selected="selected"' : ''; ?>>3:00 AM</option>
					<option value="4" <?php echo ($start_time == '4') ? 'selected="selected"' : ''; ?>>4:00 AM</option>
					<option value="5" <?php echo ($start_time == '5') ? 'selected="selected"' : ''; ?>>5:00 AM</option>
					<option value="6" <?php echo ($start_time == '6') ? 'selected="selected"' : ''; ?>>6:00 AM</option>
					<option value="7" <?php echo ($start_time == '7') ? 'selected="selected"' : ''; ?>>7:00 AM</option>
					<option value="8" <?php echo ($start_time == '8') ? 'selected="selected"' : ''; ?>>8:00 AM</option>
					<option value="9"  <?php echo ($start_time == '9' || empty($start_time)) ? 'selected="selected"' : ''; ?>>9:00 AM</option>
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
			</select> Start</label>
		</div>
		<div class="pf-element">
			<label><select class="ui-widget-content" name="event_end">
					<option value="24" <?php echo ($end_time == '24') ? 'selected="selected"' : ''; ?>>12:00 AM</option>
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
			</select> End</label>
		</div>
	</div>
	<div class="pf-element">
			<input type="hidden" name="location" value="<?php echo $this->location; ?>" />
			<?php if (isset($this->event->guid)) { ?>
			<input type="hidden" name="id" value="<?php echo $this->event->guid; ?>" />
			<input type="submit" class="ui-state-default ui-corner-all" value="Save &raquo;" /><input type="button" class="ui-state-default ui-corner-all" onclick="pines.get('<?php echo htmlentities(pines_url('com_hrm', 'editcalendar')); ?>');" value="Cancel" />
			<?php } else { ?>
			<input type="submit" class="ui-state-default ui-corner-all form_input" value="Add Event &raquo;" />
			<?php } ?>
	</div>
</form>