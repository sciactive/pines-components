<?php
/**
 * Display a form to enter calendar events.
 *
 * @package Pines
 * @subpackage com_hrm
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Zak Huber <zhuber@gmail.com>
 * @copyright Zak Huber
 * @link http://sciactive.com/
 *
 * Built upon:
 *
 * FullCalendar Created by Adam Shaw
 * http://arshaw.com/fullcalendar/
 *
 * Very Simple Context Menu Plugin by Intekhab A Rizvi
 * http://intekhabrizvi.wordpress.com/
 */
defined('P_RUN') or die('Direct access prohibited');

$this->title = (is_null($this->event->guid)) ? 'New Event' : $this->event->title;

?>
<style type="text/css" >
	/* <![CDATA[ */
	.form_text {
		width: 155px;
		text-align: center;
	}
	.form_date {
		width: 100px;
		text-align: center;
	}
	.form_select {
		width: 160px;
	}
	/* ]]> */
</style>
<script type='text/javascript'>
// <![CDATA[
	$(function(){
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
	});
// ]]>
</script>
<form class="pform" method="post" id="calendar_details" action="<?php echo pines_url('com_hrm', 'saveevent'); ?>">
	<div class="element">
		<select name="employee" class="form_select">
				<?php
				$employee_depts = explode(', ', $pines->config->com_hrm->employee_departments);
				foreach ($employee_depts as $cur_dept) {
					$cur_dept_info = explode(':', $cur_dept);
					$cur_name = $cur_dept_info[0];
					$cur_color = $cur_dept_info[1];
					$cur_select = ($this->event->employee == $cur_name) ? 'selected="selected"' : '';
					echo '<option value="'.$cur_name.':'.$cur_color.'"'.$cur_select.'>'.$cur_name.'</option>';
				}
				foreach ($this->employees as $cur_employee) {
					$cur_select = ($this->event->employee == $cur_employee->name) ? 'selected="selected"' : '';
					echo '<option value="'.$cur_employee->name.':'.$cur_employee->color.'"'.$cur_select.'>'.$cur_employee->name.'</option>';
				}
				?>
		</select>
	</div>
	<div class="element" style="padding-bottom: 0px;">
		<input class="ui-corner-all form_text" type="text" id="event_label" name="event_label" value="<?php echo (isset($this->event->label)) ? $this->event->label : 'Label'; ?>" onfocus="if(this.value==this.defaultValue)this.value=''" onblur="if(this.value=='')this.value=this.defaultValue" />
	</div>
	<?php
		if ($this->event->guid) {
			$start_date = pines_date_format($this->event->start, null, 'n/j/Y');
			$start_time = pines_date_format($this->event->start, null, 'H');
			$end_date = pines_date_format($this->event->end, null, 'n/j/Y');
			$end_time = pines_date_format($this->event->end, null, 'H');				
		}
	?>
	<script type="text/javascript">
			// <![CDATA[
			$(function(){
				$("#event_date").change(function(){
					$("#event_enddate").val($(this).val());
				}).change();
			});
			// ]]>
	</script>
	<div class="element" style="padding-bottom: 0px;">
		<span class="note">Start</span><input class="ui-corner-all form_text" type="text" id="event_date" name="event_date" value="<?php echo empty($start_date) ? date('n/j/Y') : $start_date; ?>" />
	</div>
	<div class="element">
		<span class="note">End</span><input class="ui-corner-all form_text" type="text" id="event_enddate" name="event_enddate" value="<?php echo $end_date; ?>" />
	</div>
	<div class="element">
		<script type="text/javascript">
			// <![CDATA[
			$(function(){
				var timespan = $("#timespan");
				$("#calendar_details [name=all_day]").change(function(){
					var all_day = $(this);
					if (all_day.is(":checked") && all_day.val() == "timeSpan") {
						timespan.show();
					} else if (all_day.is(":checked") && all_day.val() == "allDay") {
						timespan.hide();
					}
				}).change();
			});
			// ]]>
		</script>
		<label><input class="field ui-widget-content" type="radio" name="all_day" value="timeSpan" checked="checked" />Timespan</label>
		<label><input class="field ui-widget-content" type="radio" name="all_day" value="allDay" />All Day</label>
	</div>
	<div id="timespan" style="text-align: center;">
		<div class="element">
			<label><select name="event_start">
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
			</select> Event Start</label>
		</div>
		<div class="element">
			<label><select name="event_end">
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
			</select> Event End</label>
		</div>
	</div>
	<div class="element">
			<?php if (isset($this->event->guid)) { ?>
			<input type="hidden" name="id" value="<?php echo $this->event->guid; ?>" />
			<input type="submit" value="Save Event &raquo;" /><input type="button" onclick="pines.get('<?php echo pines_url('com_hrm', 'editcalendar'); ?>');" value="Cancel" />
			<?php } else { ?>
			<input type="submit" value="Add Event &raquo;" class="form_select" />
			<?php } ?>
	</div>
</form>